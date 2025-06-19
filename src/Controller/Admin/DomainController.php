<?php

namespace App\Controller\Admin;

use App\Entity\Domain;
use App\Repository\DomainRepository;
use App\Service\AccessTokenService;
use App\Service\GetDomainsService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DomainController extends AbstractDashboardController
{
    public function __construct(
        private DomainRepository $domRepo,
        private AccessTokenService $accessTokenService,
        private GetDomainsService $getDomainsService,
    ) {
    }

    public function index(): Response
    {
        //TODO stock accesstoken in session
        $accesstoken = $this->accessTokenService->getAccessToken();
        if ($accesstoken === '') {
            $this->addFlash('warning', 'Erreur lors de la récupération du token');
        }
        $domains = $this->getDomainsService->getDomains($accesstoken);
        $activeDomains = $this->domRepo->findBy(['isHistory' => false], ['expireAt' => 'ASC']);
        $domainsToExpire = $this->domRepo->getExpireSoon(new \DateTimeImmutable()->add(new \DateInterval('P30D')));
        $domainsToSuppress =  $this->domRepo->findBy(['isToSuppress' => true], ['expireAt' => 'ASC']);
        $toSuppressCount = $this->domRepo->getCountExpire(new \DateTimeImmutable());

        // Set Months to pass them to the twig template for the chart
        $dateNow = new \DateTimeImmutable();
        for ($i = 0; $i <= 11; $i++) {
            $months[$i] = $dateNow->format('M');
            $dateNow = $dateNow->add(new \DateInterval('P1M'));
        }

        return $this->render('admin/my-dashboard.html.twig', [
            'activeDomains' => $activeDomains,
            'domainsToExpire' => $domainsToExpire,
            'domainsToSuppress' => $domainsToSuppress,
            'toSuppressCount' => $toSuppressCount,
            'months' => $months,
            'domains' => $domains,
            'test' => $test,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Project Amandine')
            ->disableDarkMode()
        ;
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-chart-line'),

            MenuItem::linkToCrud('Domaines', 'fa fa-server', Domain::class),

            //MenuItem::section('Users', 'fa fa-user'),
        ];
    }

    public function configureUserMenu(UserInterface $user): \EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu
    {
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            ->setName($user->getUserIdentifier())
            // use this method if you don't want to display the name of the user
            ->displayUserName(true)

            // you can return an URL with the avatar image
            ->setAvatarUrl('https://images-cdn.9gag.com/photo/6204031_700b_v1.jpg') //Photo pour Simon
            // use this method if you don't want to display the user image
            // ->displayUserAvatar(false)

            // you can use any type of menu item, except submenus
            ->addMenuItems([
                MenuItem::linkToLogout('Logout', 'fa fa-sign-out'),
            ]);
    }

    public function configureAssets(): Assets
    {
        $asset =  Assets::new();

        return $asset
            ->addCssFile('build/styles/admin.css')
            ->addCssFile('assets/styles/app.css')
            ;
    }
}
