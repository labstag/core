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
        return [
            'user_username' => [
                'title'    => 'Username',
                'function' => 'replaceUserUsername',
            ],
            'link_login'    => [
                'title'    => 'Link login',
                'function' => 'replaceLinkLogin',
            ],
            'user_email'    => [
                'title'    => 'email',
                'function' => 'replaceUserEmail',
            ],
            'user_roles'    => [
                'title'    => 'Roles',
                'function' => 'replaceUserRoles',
            ],
        ];
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

    public function init(): void
    {
        $this->from('');
        $this->html('');
        $this->text('');
        $this->subject('');
    }

    public function setData(array $data = []): void
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

    protected function replaceLinkLogin()
    {
        $configuration = $this->siteService->getConfiguration();

        return $configuration->getUrl().$this->router->generate(
            'app_login',
            []
        );
    }

    protected function replaceUserEmail()
    {
        if (!isset($this->data['user'])) {
            return null;
        }

        return $this->data['user']->getEmail();
    }

    protected function replaceUserRoles()
    {
        if (!isset($this->data['user'])) {
            return null;
        }

        $roles = $this->data['user']->getRoles();

        return implode(', ', $roles);
    }

    protected function replaceUserUsername()
    {
        if (!isset($this->data['user'])) {
            return null;
        }

        return $this->data['user']->getUsername();
    }

    private function getTemplate(string $type): string
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
        $codes = $this->getCodes();
        foreach ($codes as $key => $data) {
            $content = str_replace(
                '%'.$key.'%',
                call_user_func([$this, $data['function']]),
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
