<?php

namespace Labstag\Template;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Template;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Twig\Environment;

#[AutoconfigureTag('labstag.templates')]
abstract class TemplateAbstract
{

    /**
     * @var mixed[]
     */
    protected array $templates = [];

    public function __construct(
        protected Environment $twigEnvironment,
        protected LoggerInterface $logger,
        protected EntityManagerInterface $entityManager,
    )
    {
    }

    public function getCode(): string
    {
        return '';
    }

    public function getContent(string $type): string
    {
        $code    = $this->getCode();

        return $this->twigEnvironment->render($this->getTemplateContent($code, $type), []);
    }

    public function getTemplate()
    {
        $code       = $this->getCode();
        $entityRepository = $this->entityManager->getRepository(Template::class);

        $template = $entityRepository->findOneBy(
            ['code' => $code]
        );
        if ($template instanceof Template) {
            return $template;
        }

        $html = $this->getContent('html');
        $text = $this->getContent('txt');

        $template = new Template();
        $template->setCode($code);
        $template->setTitle('Template ' . $code);
        $template->setHtml($html);
        $template->setText($text);

        $entityRepository->save($template);

        return $template;
    }

    /**
     * @return mixed[]
     */
    protected function getTemplateContent(string $code, string $type): string
    {
        if (isset($this->templates[$code][$type])) {
            return $this->templates[$code][$type];
        }

        $extension = '.' . $type . '.twig';
        $files     = [
            'generate/' . $code . $extension,
            'generate/default' . $extension,
        ];

        $view   = end($files);
        $loader = $this->twigEnvironment->getLoader();
        foreach ($files as $file) {
            if (!$loader->exists($file)) {
                continue;
            }

            $view = $file;

            break;
        }

        if ($view == end($files)) {
            $this->logger->warning(
                'Template not found',
                [
                    'code' => $code,
                    'type' => $type,
                ]
            );
        }

        $this->templates[$file][$type] = $view;

        return $this->templates[$file][$type];
    }
}
