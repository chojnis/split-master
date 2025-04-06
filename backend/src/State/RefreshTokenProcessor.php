<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\RefreshTokenService;
use App\Entity\RefreshToken;
use App\Dto\RefreshTokenRequest;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
// use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use App\Repository\RefreshTokenRepository;

class RefreshTokenProcessor implements ProcessorInterface
{
    public function __construct(
        private RefreshTokenService $refreshTokenService,
        private RefreshTokenRepository $refreshTokenRepository,
        private JWTTokenManagerInterface $jwtTokenManager,
        private SerializerInterface $serializer
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        if(!$data instanceof RefreshTokenRequest) {
            throw new InvalidArgumentException('Invalid input.');
        }

        $refreshTokenString = $data->refresh_token;

        $refreshToken = $this->refreshTokenRepository->findValidOneByToken($refreshTokenString);
        if (!$refreshToken) {
            throw new InvalidArgumentException('Invalid refresh token.');
        }

        $user = $refreshToken->getUser();

        $return = [];
        $return['token'] = $this->jwtTokenManager->create($user);

        $this->refreshTokenService->revokeRefreshToken($refreshToken);
        
        $return['refresh_token'] = $this->refreshTokenService->generateRefreshToken($user)->getRefreshToken();
        $return['user'] = $this->serializer->normalize(
            $user, 
            null, 
            ['groups' => ['user:read']]
        );

        return $return;
    }
}
