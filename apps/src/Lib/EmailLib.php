<?php

namespace Labstag\Lib;

use Labstag\Entity\Template;
use Labstag\Replace\LinkLoginReplace;
use Labstag\Replace\UserEmailReplace;
use Labstag\Replace\UsernameReplace;
use Labstag\Replace\UserRolesReplace;
use Labstag\Repository\TemplateRepository;
use Labstag\Service\ConfigurationService;
use Labstag\Service\SiteService;
use Labstag\Service\WorkflowService;
use Override;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

abstract class EmailLib extends Email
{

    /**
     * @var mixed[]
     */
    protected array $data = [];

    /**
     * @var mixed[]
     */
    protected array $templates = [];

    public function __construct(
        /**
         * @var object[]
         */
        #[AutowireIterator('labstag.replaces')]
        private readonly iterable $replaces,
        protected RouterInterface $router,
        protected SiteService $siteService,
        protected ConfigurationService $configurationService,
        protected WorkflowService $workflowService,
        protected Environment $twigEnvironment,
        protected TemplateRepository $templateRepository,
    )
    {
        parent::__construct();
    }

    #[Override]
    public function from(Address|string ...$addresses): static
    {
        $configuration = $this->configurationService->getConfiguration();
        $addresses     = $configuration->getNoReply();

        return parent::from($addresses);
    }

    public function getEntity(): ?Template
    {
        return $this->templateRepository->findOneBy(
            [
                'code' => $this->getType(),
            ]
        );
    }

    public function getHelp(): ?string
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

    /**
     * @return string[]
     */
    public function getReplaces(): array
    {
        return [
            UsernameReplace::class,
            LinkLoginReplace::class,
            UserEmailReplace::class,
            UserRolesReplace::class,
        ];
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

    /**
     * @param mixed[] $data
     */
    public function setData(array $data = []): void
    {
        $this->data = $data;
    }

    public function setHtml(): string
    {
        if ('' === $this->getType()) {
            return '';
        }

        return $this->getTemplate('html');
    }

    public function setText(): string
    {
        if ('' === $this->getType()) {
            return '';
        }

        return $this->getTemplate('txt');
    }

    #[Override]
    public function subject(string $subject): static
    {
        $configuration = $this->configurationService->getConfiguration();
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

    /**
     * @return mixed[]
     */
    protected function getTemplateContent(string $folder, string $type): array
    {
        if (isset($this->templates[$type])) {
            return $this->templates[$type];
        }

        $twig  = '.' . $type . '.twig';
        $files = [
            'emails/' . $folder . $twig,
            'emails/default' . $twig,
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

    private function getReplace(mixed $data): ?object
    {
        $replace = null;
        foreach ($this->replaces as $row) {
            if (!$row instanceof $data) {
                continue;
            }

            $replace = $row;

            break;
        }

        return $replace;
    }

    /**
     * @return mixed[]
     */
    private function getReplacesClass(): array
    {
        $data     = [];
        $replaces = $this->getReplaces();
        foreach ($replaces as $replace) {
            $data[] = $this->getReplace($replace);
        }

        return $data;
    }

    private function getTemplate(string $type): string
    {
        $templates = $this->templates($type);
        $this->getReplacesClass();

        return $this->twigEnvironment->render(
            $templates['view'],
            [
                'codes' => $this->getReplacesClass(),
                'type'  => $this->getType(),
                'code'  => $type,
            ]
        );
    }

    private function replace(string $content): string
    {
        $codes = $this->getReplacesClass();
        foreach ($codes as $code) {
            $code->setData($this->data);
            $content = str_replace('%' . $code->getCode() . '%', $code->exec(), $content);
        }

        return $content;
    }

    /**
     * @return mixed[]
     */
    private function templates(string $type): array
    {
        return $this->getTemplateContent($this->getType(), $type);
    }
}
