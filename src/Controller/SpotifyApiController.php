<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\SpotifyApiHelper;

/**
* @Route("/spotify-api")
*/
class SpotifyApiController extends AbstractController
{

    private $spotifyApiHelper;

    public function __construct(SpotifyApiHelper $spotifyApiHelper)
    {
        $this->spotifyApiHelper = $spotifyApiHelper;
    }

    /**
     * @Route(
     *      "/login",
     *      name="spotify-api-login"
     * )
     */
    public function login(Request $request)
    {
        return $this->redirect($this->spotifyApiHelper->getAuthorizeUrl());
    }

    /**
     * @Route(
     *      "/auth-callback",
     *      name="spotify-api-auth-callback"
     * )
     */
    public function authCallback(Request $request)
    {
        $authorizationCode = $request->query->get('code');
        $data = $this->spotifyApiHelper->getTokenFromAuthorizationCode($authorizationCode);

        $accessTokenCookie = Cookie::create('access_token', $data['access_token'], strtotime($data['expires_in'] . ' seconds'), '/', 'localhost', null, false);
        $refreshTokenCookie = Cookie::create('refresh_token', $data['refresh_token'], strtotime('tomorrow'), '/', 'localhost', null, false);

        $response = $this->json($data);
        $response->headers->setCookie($accessTokenCookie);
        $response->headers->setCookie($refreshTokenCookie);

        return $response;
    }
}
