<?php

declare(strict_types=1);

namespace EcobrotboxB2B\Subscriber;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use EcobrotboxB2B\EcobrotboxB2B;

/**
 * Blocks the checkout in accordance to the plugin settings
 */
class Checkout implements EventSubscriberInterface
{
    private SystemConfigService $configService;

    /**
     * @var array<string, mixed>
     */
    private array $config;

    private ?CustomerEntity $customer;

    private Request $request;

    private UrlGeneratorInterface $router;

    public function __construct(
        SystemConfigService $configService,
        RequestStack $requestStack,
        UrlGeneratorInterface $router
    ) {
        $this->configService = $configService;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->customer = null;
        $this->config = [];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'onPageLoaded',
        ];
    }

    public function onPageLoaded(StorefrontRenderEvent $event): void
    {
        $parameters = $event->getParameters();
        $page = $parameters['page'] ?? null;
        if (empty($page)) {
            return;
        }

        $this->customer = $event->getSalesChannelContext()->getCustomer();
        $this->setConfiguration(
            $event->getSalesChannelContext()->getSalesChannelId()
        );


        $this->blockShop($event->getRequest());

        // TODO: prüfen ob alles außer die Startseite &
        // Registrieren Seite auf den Login weitergeleitet werden soll

        $hidePrices = $this->hidePrices();

        if ($hidePrices) {
            $blockCheckout = true;
        } else {
            $blockCheckout = $this->blockCheckout();
        }

        $page->assign([
            'EcobrotboxB2B' => [
                'hidePrices' => $hidePrices,
                'blockCheckout' => $blockCheckout
            ]
        ]);
    }

    private function blockCheckout(): bool
    {
        return !$this->isUserAllowed('checkoutAllowedForCustomerGroups');
    }

    /**
     * This method blocks every request except the frontpage and the login page.
     * @return void
     */
    private function blockShop(Request $request): void
    {
        $userAllowed = $this
            ->isUserAllowed('shopAllowedForCustomerGroups')
        ;

        if ($userAllowed) {
            return;
        }

        $allowedRoutes = [
            'frontend.home.page',
            'frontend.checkout.info',         // AJAX cart info
            'frontend.cart.offcanvas',        // offcanvas cart
            'frontend.account.login.page',    // Login page
            'frontend.account.login',         // Login controller
            'frontend.account.register.page', // Register page
            'frontend.account.register.save', // Registration controller
        ];

        $route = $request->attributes->get('_route');

        // block AJAX search requests
        if ('frontend.search.suggest' == $route) {
            $response = new Response(null, 404);
            $response->send();
        }

        // if it's an allowed route or an account page
        if (
            in_array($route, $allowedRoutes) ||
            str_starts_with($route, 'frontend.account.')
        ) {
            return;
        }

        // redirect to homepage
        $target = $this->router->generate('frontend.home.page');
        $response = new RedirectResponse($target, 302);
        $response->send();
    }

    private function isUserAllowed(string $configField): bool
    {
        $allowedGroups = $this->config[$configField] ?? [];
        // if the grouplist is empty, the user is allowed
        if (empty($allowedGroups)) {
            return true;
        }
        // if the grouplist is NOT empty and
        // the user is not logged in, he is out
        if (!$this->customer instanceof CustomerEntity) {
            return false;
        }
        $customerGroupId = $this->customer->getGroupId();
        return in_array($customerGroupId, $allowedGroups);
    }

    private function hidePrices(): bool
    {
        return !$this->isUserAllowed('checkoutAllowedForCustomerGroups');
    }

    private function setConfiguration(string $salesChannelId): self
    {
        $this->config = $this
            ->configService
            ->get(
                EcobrotboxB2B::NAME . '.config',
                $salesChannelId
            )
        ;
        return $this;
    }
}
