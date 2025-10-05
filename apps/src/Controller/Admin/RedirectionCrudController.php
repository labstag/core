<?php

namespace Labstag\Controller\Admin;

use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Redirection;
use Labstag\Form\Admin\RedirectionImportType;
use Labstag\Repository\RedirectionRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;
use ZipArchive;

class RedirectionCrudController extends AbstractCrudControllerLib
{
    private const FIELDCSV = 2;

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);
        $this->configureActionsTestSource($actions);
        $this->configureActionsImport($actions);
        $this->configureActionsExport($actions);

        return $actions;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield $this->crudFieldFactory->idField();
        $textField = TextField::new('source', new TranslatableMessage('Source'));
        if (Action::NEW === $pageName) {
            $request       = $this->requestStack->getCurrentRequest();
            $defaultSource = $request->query->get('source', '');
            $textField->setFormTypeOptions(
                ['data' => $defaultSource]
            );
        }

        yield $textField;
        yield TextField::new('destination', new TranslatableMessage('Destination'));
        yield IntegerField::new('action_code', new TranslatableMessage('Action code'));
        yield $this->crudFieldFactory->booleanField('regex', (string) new TranslatableMessage('Regex'), false)->hideOnForm();
        yield $this->crudFieldFactory->booleanField('regex', (string) new TranslatableMessage('Regex'), false)->hideOnIndex();
        yield $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable'));
        yield IntegerField::new('last_count', new TranslatableMessage('Last count'))->hideonForm();
        foreach ($this->crudFieldFactory->dateSet() as $field) {
            yield $field;
        }
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);

        return $filters;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Redirection
    {
        $redirection = new $entityFqcn();
        $redirection->setActionType('url');
        $redirection->setPosition(0);
        $redirection->setActionCode(301);

        return $redirection;
    }

    public function export(RedirectionRepository $redirectionRepository): void
    {
        $all    = $redirectionRepository->findAll();
        $row    = [];
        $header = [
            'Source',
            'Destination',
        ];
        foreach ($all as $redirection) {
            $tab   = [
                $redirection->getSource(),
                $redirection->getDestination(),
            ];
            $row[] = $tab;
        }

        $response = $this->sendToExport($header, $row);

        $response->send();
    }

    public static function getEntityFqcn(): string
    {
        return Redirection::class;
    }

    public function import(Request $request, RedirectionRepository $redirectionRepository): RedirectResponse|Response
    {
        $form = $this->createForm(
            RedirectionImportType::class,
            null,
            [
                'attr' => ['id' => 'redirection_import'],
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $data = $this->importCsv($file, $redirectionRepository);

            foreach ($data as $row) {
                $redirectionRepository->persist($row);
            }

            $redirectionRepository->flush();

            return $this->redirectToIndex();
        }

        return $this->render(
            'admin/redirection/import.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route('/admin/redirection/{entity}/test', name: 'admin_redirection_test')]
    public function testSource(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $redirection                = $serviceEntityRepositoryLib->find($entity);

        return $this->redirect($redirection->getSource());
    }

    protected function redirectToIndex(): RedirectResponse
    {
        $generator = $this->container->get(AdminUrlGenerator::class);
        $generator->setController(self::class);
        $generator->setAction(Action::INDEX);

        return $this->redirect($generator->generateUrl());
    }

    /**
     * @param array<int, string> $header
     * @param list<array<int, string|null>> $rows
     */
    protected function sendToExport(array $header, array $rows): Response
    {
        $tempZip    = tmpfile();
        $now        = new DateTime('now');
        $metaZip    = stream_get_meta_data($tempZip);
        $zipArchive = new ZipArchive();
        $zipArchive->open($metaZip['uri']);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->fromArray($header, null, 'A1');
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A2');

        try {
            foreach (['Xlsx', 'Xls', 'Ods'] as $writerType) {
                $path   = $this->getFilename($now->format('Ymd') . '-export.', mb_strtolower($writerType));
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $writer->save($path);
                $zipArchive->addFile($path, basename((string) $path));
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }

        $zipArchive->close();

        return new Response(
            file_get_contents($metaZip['uri']),
            Response::HTTP_OK,
            [
                'Content-Type'        => 'application/x-zip',
                'Content-Disposition' => 'attachment; filename="' . $now->format('Ymd') . '-export.zip"',
                'Cache:Control'       => 'no-cache, must-revalidate',
                'Expires'             => 'Mon, 26 Jul 1997 05:00:00 GMT',
                'Last-Modified'       => gmdate('D, d M Y H:i:s') . ' GMT',
                'Pragma'              => 'no-cache',
            ]
        );
    }

    private function configureActionsExport(Actions $actions): void
    {
        $action = Action::new('export', 'Exporter', 'fas fa-file-export');
        $action->addCssClass('btn btn-primary');
        $action->linkToCrudAction('export');
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function configureActionsImport(Actions $actions): void
    {
        $action = Action::new('import', 'Importer', 'fas fa-file-import');
        $action->addCssClass('btn btn-primary');
        $action->linkToCrudAction('import');
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function configureActionsTestSource(Actions $actions): void
    {
        $action = Action::new('testSource', 'Test de la source');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToUrl(
            fn ($entity): string => $this->generateUrl(
                'admin_redirection_test',
                [
                    'entity' => $entity->getId(),
                ]
            )
        );

        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function getFilename(string $filename, string $extension = 'xlsx'): string
    {
        $originalExtension = pathinfo($filename, PATHINFO_EXTENSION);

        return $this->getTemporaryFolder() . '/' . str_replace(
            '.' . $originalExtension,
            '.' . $extension,
            basename($filename)
        );
    }

    private function getTemporaryFolder(): string
    {
        $tempFolder = sys_get_temp_dir();
        if (!is_dir($tempFolder) && (!mkdir($tempFolder) && !is_dir($tempFolder))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $tempFolder));
        }

        return $tempFolder;
    }

    /**
     * @return Redirection[]
     */
    private function importCsv(\Symfony\Component\HttpFoundation\File\UploadedFile $file, RedirectionRepository $redirectionRepository): array
    {
        $data        = [];
        $csv         = new Csv();
        $spreadsheet = $csv->load($file->getPathname());
        $sheetData   = $spreadsheet->getActiveSheet()->toArray();
        $head        = $sheetData[0];
        $find        = $this->setFind($head);
        if (self::FIELDCSV != $find) {
            $this->addFlash('danger', 'Le fichier n\'est pas correctement formaté');

            return $data;
        }

        $head = array_flip($head);

        $sheetData = array_slice($sheetData, 1);
        foreach ($sheetData as $row) {
            $source      = parse_url((string) $row[$head['Source']]);
            $destination = $row[$head['Destination']];
            $source      = $source['path'];
            $source .= isset($source['query']) ? '?' . $source['query'] : '';
            $redirection = $redirectionRepository->findOneBy(
                ['source' => $source]
            );
            if (null === $redirection) {
                $redirection = new Redirection();
                $redirection->setActionCode(301);
                $redirection->setSource($source);
                $redirection->setEnable(true);
            }

            $redirection->setDestination($destination);

            $data[] = $redirection;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $head
     */
    private function setFind(array $head): int
    {
        $find = 0;
        foreach ($head as $key => $value) {
            if ((0 == $key && 'Source' == $value) || (1 == $key && 'Destination' == $value)) {
                ++$find;
            }
        }

        return $find;
    }
}
