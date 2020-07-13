<?php


namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Guard\AuthenticatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Guard\Token\GuardTokenInterface;

class JwtTokenAuthenticator implements AuthenticatorInterface
{

    private $jwtEncoder;

    private $em;

    public function __construct(JWTEncoderInterface $jwtEncoder, EntityManagerInterface $em)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->em = $em;
    }

    public function getCredentials(Request $request)
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );

        $token = $extractor->extract($request);
        if (!$token) {
            return;
        }
        return $token;
    }
    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        $data = $this->jwtEncoder->decode($credentials);

        if ($data === false) {
            throw new CustomUserMessageAuthenticationException('Invalid Token', [], 401);
        }

        $username = $data['username'];
        $user = $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $username]);

        //var_dump($user->getRoles());die(' ==> Paso por getUser');
        return $user;
    }
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new CustomUserMessageAuthenticationException(strtr($exception->getMessageKey(), $exception->getMessageData()));
    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        throw new CustomUserMessageAuthenticationException('Authentication Required', [], 401);
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        // TODO: Implement supports() method.
    }
    /**
     * Create an authenticated token for the given user.
     *
     * If you don't care about which token class is used or don't really
     * understand what a "token" is, you can skip this method by extending
     * the AbstractGuardAuthenticator class from your authenticator.
     *
     * @param string $providerKey The provider (i.e. firewall) key
     *
     * @return GuardTokenInterface
     * @see AbstractGuardAuthenticator
     *
     */
    public function createAuthenticatedToken(UserInterface $user, $providerKey)
    {
        // TODO: Implement createAuthenticatedToken() method.
    }
}