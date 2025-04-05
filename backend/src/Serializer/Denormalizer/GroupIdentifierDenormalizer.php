<?php

namespace App\Serializer\Denormalizer;

use ApiPlatform\Symfony\Routing\IriConverter;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Group;
use App\Entity\Currency;

class GroupIdentifierDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private IriConverter $iriConverter, 
        private EntityManagerInterface $entityManager
    ) {}

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $data['currency'] = $this->iriConverter->getIriFromResource(resource: Currency::class, context: ['uri_variables' => ['id' => $data['currency']]]);

        return $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return 
            \in_array($format, ['json', 'jsonld'], true) 
            && is_a($type, Group::class, true)
            && !empty($data['currency'])
            && !isset($context[__CLASS__]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            'object' => null,
            '*' => false,
            Group::class => true
        ];
    }
}