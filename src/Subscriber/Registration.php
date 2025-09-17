<?php

declare(strict_types=1);

namespace EcobrotboxB2B\Subscriber;

use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CustomerGroupAssignment
 *
 * Changes the customer group based on the customer account type
 */
class Registration implements EventSubscriberInterface
{
    /**
     * @var EntityRepository
     */
    private $customerRepository;

    /**
     * @var Request
     */
    private $request;

    private Session $session;

    private SystemConfigService $configService;

    private LoggerInterface $logger;

    private TranslatorInterface $translator;

    private UrlGeneratorInterface $router;

    private RequestStack $requestStack;

    private string $redirectTarget;

    public function __construct(
        EntityRepository $customerRepository,
        RequestStack $requestStack,
        SystemConfigService $configService,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        UrlGeneratorInterface $router
    ) {
        $this->customerRepository = $customerRepository;
        $this->requestStack = $requestStack;
        $this->configService = $configService;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->router = $router;
        $this->redirectTarget = '';
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CustomerRegisterEvent::class => 'onRegistration'
        ];
    }

    /**
     * @param CustomerRegisterEvent $event
     * @return void
     */
    public function onRegistration(CustomerRegisterEvent $event)
    {
        try {
            $this->assignCustomerGroup($event);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        try {
            $this->requestCustomerGroupChange($event);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        try {
            $this->deactivateCustomer($event);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        if ('' != $this->redirectTarget) {
            $response = new RedirectResponse($this->redirectTarget, 302);
            $response->send();
        }
    }

    /**
     * Changes the customer group at registration
     *
     * @param CustomerRegisterEvent $event
     * @throws Exception
     */
    public function assignCustomerGroup(CustomerRegisterEvent $event): void
    {
        // check if the customer registers private or as a business
        $accountType = $this->getAccountType($event->getCustomer());
        // get plugin configuration
        $config = $this->getConfiguration($event->getSalesChannelId());
        // privateNewCustomerGroup or businessNewCustomerGroup
        $newGroupField = $accountType . 'NewCustomerGroup';
        // check if the config field is available
        if (empty($config[$newGroupField] ?? false)) {
            return;
        }
        // get new customer group for the corresponding account type
        $newCustomerGroup = $config[$newGroupField];
        // check if customer is already in the correct group
        if ($event->getCustomer()->getGroupId() == $newCustomerGroup) {
            return;
        }
        // update user
        $this->customerRepository->update([
            [
                'id' => $event->getCustomer()->getId(),
                'groupId' => $newCustomerGroup
            ]
        ], $event->getContext());
    }

    public function requestCustomerGroupChange(CustomerRegisterEvent $event): void
    {
        // check if the customer registers private or as a business
        $accountType = $this->getAccountType($event->getCustomer());
        // get plugin configuration
        $config = $this->getConfiguration($event->getSalesChannelId());
        // privateNewCustomerGroup or businessNewCustomerGroup
        $newGroupField = $accountType . 'RequestNewCustomerGroup';
        // check if the config field is available
        if (empty($config[$newGroupField] ?? false)) {
            return;
        }
        // get new customer group for the corresponding account type
        $requestedGroup = $config[$newGroupField];
        // check if customer is already in the correct group
        if ($event->getCustomer()->getGroupId() == $requestedGroup) {
            return;
        }
        // update user
        $this->customerRepository->update([
            [
                'id' => $event->getCustomer()->getId(),
                'requestedGroupId' => $requestedGroup
            ]
        ], $event->getContext());

        $this->redirect('frontend.account.home.page');
    }

    public function deactivateCustomer(CustomerRegisterEvent $event): void
    {
        // check if the customer registers private or as a business
        $accountType = $this->getAccountType($event->getCustomer());
        // get plugin configuration
        $config = $this->getConfiguration($event->getSalesChannelId());
        // privateNewCustomerGroup or businessNewCustomerGroup
        $deactivateField = $accountType . 'DeactivateAccount';
        // check if the config field is available
        if (empty($config[$deactivateField] ?? false)) {
            return;
        }

        $this->customerRepository->update([
            [
                'id' => $event->getCustomer()->getId(),
                'active' => false
            ]
        ], $event->getContext());

        $this->redirectWithMessage(
            'ecobrotbox.b2b.registration.deactivated',
            'frontend.account.logout.page'
        );
    }

    private function addMessage(string $message): void
    {
        $this
            ->requestStack
            ->getSession()
            ->getFlashBag()
            ->add(
                'info',
                $this->translator->trans($message)
            )
        ;
    }

    /**
     * @param string|null $salesChannelId
     * @return array<string, mixed>
     */
    private function getConfiguration(?string $salesChannelId = null): array
    {
        return $this
            ->configService
            ->get(
                'EcobrotboxB2B.config',
                $salesChannelId
            )
        ;
    }

    private function getAccountType(CustomerEntity $customer): string
    {
        $availabeTypes = [
            CustomerEntity::ACCOUNT_TYPE_PRIVATE,
            CustomerEntity::ACCOUNT_TYPE_BUSINESS
        ];
        // check if the customer registers private or as a business
        $type = $customer->getAccountType();

        if (!in_array($type, $availabeTypes)) {
            throw new Exception('No customer group given');
        }
        return $type;
    }

    /**
     * This message creates a flash message and sets the redirectTarget
     * attribute that is used in the onResponse method to create a redirection.
     * The flash message is shown on the redirected page.
     *
     * @param string $message
     * @param string $route
     * @return void
     */
    private function redirectWithMessage(
        string $message,
        string $route
    ): void {
        $this->addMessage($message);
        $this->redirect($route);
    }

    private function redirect(string $route): void
    {
        $this->redirectTarget = $this->router->generate($route);
    }
}
