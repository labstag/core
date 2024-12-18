<?php

namespace Labstag\Lib;

use Labstag\Repository\TemplateRepository;
use Labstag\Service\SiteService;
use Labstag\Service\WorkflowService;
use Override;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

abstract class EmailLib extends Email
{

    public $template;

    protected array $data = [];

    protected array $templates = [];

    public function __construct(
        protected RouterInterface $router,
        protected SiteService $siteService,
        protected WorkflowService $workflowService,
        protected Environment $twigEnvironment,
        protected TemplateRepository $templateRepository
    )
    {
        parent::__construct();
    }

    #[Override]
    public function from(Address|string ...$addresses): static
    {
        $configuration = $this->siteService->getConfiguration();
        $addresses     = $configuration->getNoReply();

        return parent::from($addresses);
    }

    public function getCodes()
    {
        return [];
    }

    public function getEntity()
    {
        return $this->templateRepository->findOneBy(
            [
                'code' => $this->getType(),
            ]
        );
    }

    public function getHelp()
    {
        if ('' === $this->getType()) {
            return null;
        }

        return $this->getTemplate('help.html');
    }

    public function getName(): string
    {
        return '';
    }

    public function getType(): string
    {
        return '';
    }

    #[Override]
    public function html($body, string $charset = 'utf-8'): static
    {
        $entity = $this->getEntity();
        $body   = $this->replace($entity->getHtml());

        return parent::html($body, $charset);
    }

    public function init()
    {
        $this->from('');
        $this->html('');
        $this->text('');
        $this->subject('');
    }

    public function setData(array $data = [])
    {
        $this->data = $data;
    }

    public function setHtml()
    {
        if ('' === $this->getType()) {
            return null;
        }

        return $this->getTemplate('html');
    }

    public function setText()
    {
        if ('' === $this->getType()) {
            return null;
        }

        return $this->getTemplate('txt');
    }

    #[Override]
    public function subject(string $subject): static
    {
        $configuration = $this->siteService->getConfiguration();
        $entity        = $this->getEntity();
        $subject       = str_replace(
            [
                '%content_title%',
                '%site_name%',
            ],
            [
                $this->replace($entity->getTitle()),
                $configuration->getName(),
            ],
            $configuration->getTitleFormat()
        );
        

        return parent::subject($subject);
    }

    #[Override]
    public function text($body, string $charset = 'utf-8'): static
    {
        $entity = $this->getEntity();
        $body   = $this->replace($entity->getText());

        return parent::text($body, $charset);
    }

    protected function getReplaces()
    {
        return [
            'user_username' => 'replaceUserUsername',
            'user_email'    => 'replaceUserEmail',
            'user_roles'    => 'replaceUserRoles',
        ];
    }

    protected function getTemplateContent(string $folder, string $type)
    {
        if (isset($this->template[$type])) {
            return $this->templates[$type];
        }

        $twig  = '.'.$type.'.twig';
        $files = [
            'emails/'.$folder.$twig,
            'emails/default'.$twig,
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

        $this->templates[$type] = [
            'hook'  => 'paragraph',
            'type'  => $type,
            'files' => $files,
            'view'  => $view,
        ];

        return $this->templates[$type];
    }

    protected function replaceUserEmail()
    {
        return $this->data['user']->getEmail();
    }

    protected function replaceUserRoles()
    {
        $roles = $this->data['user']->getRoles();

        return implode(', ', $roles);
    }

    protected function replaceUserUsername()
    {
        return $this->data['user']->getUsername();
    }

    private function getTemplate($type)
    {
        $templates = $this->templates($type);

        return $this->twigEnvironment->render(
            $templates['view'],
            [
                'codes' => $this->getCodes(),
                'type'  => $this->getType(),
                'code'  => $type,
            ]
        );
    }

    private function replace($content)
    {
        $data = $this->getReplaces();
        foreach ($data as $key => $function) {
            $content = str_replace(
                '%'.$key.'%',
                call_user_func([$this, $function]),
                $content
            );
        }

        return $content;
    }

    private function templates(string $type): array
    {
        return $this->getTemplateContent($this->getType(), $type);
    }
}
