<?php

namespace Labstag\Controller\Admin;

use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Labstag\Entity\Redirection;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Lib\AbstractCrudControllerLib;
use Labstag\Repository\RedirectionRepository;
use Override;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;
use ZipArchive;

class RedirectionCrudController extends AbstractCrudControllerLib
{
    private const FIELDCSV = 2;

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);
        $this->configureActionsTestSource($actions);
        $this->configureActionsImport($actions);
        $this->configureActionsExport($actions);

        return $actions;
    }

    private function configureActionsExport(Actions $actions): void
    {
        $export = Action::new('export', 'Exporter', 'fas fa-file-export');
        $export->addCssClass('btn btn-primary');
        $export->linkToCrudAction('export');
        $export->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $export);
    }

    public function export(RedirectionRepository $redirectionRepository)
    {
        $all    = $redirectionRepository->findAll();
        $row    = [];
        $header = [
            'Source',
            'Destination',
        ];
        foreach ($all as $redirection) {
            $tab = [
                $redirection->getSource(),
                $redirection->getDestination(),
            ];
            $row[] = $tab;
        }

        $response = $this->sendToExport($header, $row);

        $response->send();
    }

    protected function sendToExport($header, $rows): Response
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
                $path   = $this->getFilename($now->format('Ymd').'-export.', mb_strtolower($writerType));
                $writer = IOFactory::createWriter($spreadsheet, $writerType);
                $writer->save($path);
                $zipArchive->addFile($path, basename($path));
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }

        $zipArchive->close();

        return new Response(
            file_get_contents($metaZip['uri']),
            200,
            [
                'Content-Type'        => 'application/x-zip',
                'Content-Disposition' => 'attachment; filename="'.$now->format('Ymd').'-export.zip"',
                'Cache:Control'       => 'no-cache, must-revalidate',
                'Expires'             => 'Mon, 26 Jul 1997 05:00:00 GMT',
                'Last-Modified'       => gmdate('D, d M Y H:i:s').' GMT',
                'Pragma'              => 'no-cache',
            ]
        );
    }

    private function getFilename($filename, $extension = 'xlsx')
    {
        $originalExtension = pathinfo($filename, PATHINFO_EXTENSION);

        return $this->getTemporaryFolder().'/'.str_replace('.'.$originalExtension, '.'.$extension, basename($filename));
    }

    private function getTemporaryFolder()
    {
        $tempFolder = sys_get_temp_dir();
        if (!is_dir($tempFolder) && (!mkdir($tempFolder) && !is_dir($tempFolder))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $tempFolder));
        }

        return $tempFolder;
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
        $test = Action::new('testSource', 'Test de la source');
        $test->setHtmlAttributes(
            ['target' => '_blank']
        );
        $test->linkToCrudAction('testSource');

        $actions->add(Crud::PAGE_DETAIL, $test);
        $actions->add(Crud::PAGE_EDIT, $test);
        $actions->add(Crud::PAGE_INDEX, $test);
    }

    public function import(Request $request, RedirectionRepository $redirectionRepository)
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
            $file     = $form->get('file')->getData();
            $mimeType = $file->getMimeType();
            $data     = $this->importCsv($file, $redirectionRepository);

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

    protected function redirectToIndex()
    {
        $generator = $this->container->get(AdminUrlGenerator::class);
        $generator->setController(RedirectionCrudController::class);
        $generator->setAction(Action::INDEX);

        return $this->redirect($generator->generateUrl());
    }

    private function importCsv($file, RedirectionRepository $redirectionRepository): array
    {
        $data = [];
        $csv  = new Csv();

        $spreadsheet = $csv->load($file->getPathname());
        $sheetData   = $spreadsheet->getActiveSheet()->toArray();

        $head = $sheetData[0];
        $find = 0;
        foreach ($head as $key => $value) {
            if ((0 == $key && 'Source' == $value) || (1 == $key && 'Destination' == $value)) {
                ++$find;
            }
        }

        if (self::FIELDCSV != $find) {
            $this->addFlash('danger', 'Le fichier n\'est pas correctement formaté');

            return $data;
        }

        $head = array_flip($head);

        $sheetData = array_slice($sheetData, 1);
        foreach ($sheetData as $row) {
            $source      = parse_url($row[$head['Source']]);
            $destination = $row[$head['Destination']];
            $source      = $source['path'];
            $source .= isset($source['query']) ? '?'.$source['query'] : '';
            $redirection = $redirectionRepository->findOneBy(
                ['source' => $source]
            );
            if (!$redirection) {
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

    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterEnable($filters);

        return $filters;
    }
    
    public static function getEntityFqcn(): string
    {
        return Redirection::class;
    }

    public function testSource(AdminContext $adminContext): RedirectResponse
    {
        $redirection = $adminContext->getEntity()->getInstance();

        return $this->redirect($redirection->getSource());
    }

    public function configureFields(string $pageName): iterable
    {
        yield $this->addFieldID();
        yield TextField::new('source', new TranslatableMessage('Source'));
        yield TextField::new('destination', new TranslatableMessage('Destination'));
        yield IntegerField::new('action_code', new TranslatableMessage("Code d'action"));
        yield $this->addFieldBoolean('regex', new TranslatableMessage('Regex'))->renderAsSwitch(false)->hideOnForm();
        yield $this->addFieldBoolean('regex', new TranslatableMessage('Regex'))->hideOnIndex();
        yield $this->addFieldBoolean('enable', new TranslatableMessage('Activé'));
        yield IntegerField::new('last_count', new TranslatableMessage('Dernier compteur'))->hideonForm();
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
    }

    public function createEntity(string $entityFqcn): Redirection
    {
        $redirection = new $entityFqcn();
        $redirection->setActionCode(301);

        return $redirection;
    }
}
