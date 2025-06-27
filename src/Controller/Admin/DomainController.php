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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use function PHPUnit\Framework\matchesRegularExpression;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DomainController extends AbstractDashboardController
{
    public function __construct(
        private DomainRepository $domRepo,
        private AccessTokenService $accessTokenService,
        private GetDomainsService $getDomainsService,
        private RequestStack $requestStack,
    ) {
    }

    public function index(): Response
    {
        $accesstoken = $this->accessTokenService->getAccessToken();

        //TODO stock accessToken in session (4min 30s then request new accessToken)
        $session = $this->requestStack->getSession();
        $session->set('accessToken', $accesstoken);

        if ($accesstoken === '') {
            $this->addFlash('warning', 'Erreur lors de la récupération du token');
        }

        $request = $this->requestStack->getCurrentRequest();
        $domains = $this->getDomainsService->getDomains($accesstoken); // here to verify that the API domains are the same as the DB
        $activeDomains = $this->domRepo->findBy(['isHistory' => false], ['expireAt' => 'ASC']);
        $domainsToExpire = $this->domRepo->getExpireSoon(new \DateTimeImmutable()->add(new \DateInterval('P30D')));
        $domainsToSuppress =  $this->domRepo->findBy(['isToSuppress' => true], ['expireAt' => 'ASC']);
        $toSuppressCount = $this->domRepo->getCountExpire(new \DateTimeImmutable());

        $form = $this->createFormBuilder()
            ->add('domaine', TextType::class, [
                'constraints' => new Regex([
                    'pattern' => '/.*\.fr$/',
                    'match' => true,
                    'message' => 'Le nom de domaine doit terminer par ".fr"',
                ]),
            ])
            ->add('Verifier', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $domainName = $data['domaine'];

            return $this->redirectToRoute('app_check_available', ['domainName' => $domainName]);
        }

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
            'form' => $form,
            'domains' => $domains, // here to verify that the API domains are the same as the DB
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
            MenuItem::section('Pages', 'fa fa-newspaper'),
            MenuItem::linkToDashboard('Dashboard', 'fa fa-chart-line'),
            MenuItem::linkToCrud('Domaines', 'fa fa-server', Domain::class),

            MenuItem::section('Fonctions', 'fa fa-robot'),
            MenuItem::linkToRoute('Import API to DB', 'fa fa-database', 'app_database_import'),
            //TODO js for checking disponibility (modal that opens a text field that points toward CheckDomainController)
//            MenuItem::linkToRoute('Vérifier disponibilité nom de domaine', 'fa fa-clipboard-check', 'app_check_available'),
            //TODO confirmation renforcée ++ pour l'effacement de tout les domaines (popup + écrire "supprimer tous les domaines")
            MenuItem::linkToRoute('Supprimer tous les domaines', 'fa fa-trash-can', 'app_database_delete_api')
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

            ;
    }

    public function configureAssets(): Assets
    {
        $asset =  Assets::new();

        return $asset
            ->addCssFile('build/styles/admin.css')
            ;
    }
}
