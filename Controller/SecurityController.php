<?php

namespace Diside\SecurityBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\SecurityContext;
use Diside\SecurityBundle\Exception\InactiveUserException;
use Diside\SecurityBundle\Exception\UndefinedUsernameException;

class SecurityController extends Controller
{

    /**
     * @Route("/login", name="login")
     * @Template
     */
    public function loginAction(Request $request)
    {
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $error = '';
        $session = $this->get('session');

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($error) {
            $error = $this->formatError($error);
        }

        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $csrfToken = $this->container->has('form.csrf_provider')
            ? $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate')
            : null;

        $targetPath = $request->headers->get('referer');

        return array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
            'target_path' => $targetPath
        );
    }

    /**
     * @Route("/login_check", name="login_check", options={"i18n"=false})
     */
    public function loginCheckAction()
    {
        throw new \Exception("Undefined method");
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        throw new \Exception('Undefined method');
    }

    private function formatError(\Exception $error)
    {
        if ($error instanceof UndefinedUsernameException)
            return 'error.empty_email';

        if ($error instanceof InactiveUserException)
            return 'error.inactive_user';

        if ($error instanceof UsernameNotFoundException)
            return 'error.not_found';

        if ($error instanceof BadCredentialsException)
            return 'error.bad_credentials';

        var_dump($error->getMessage());
        throw new \Exception(get_class($error));
    }
}
