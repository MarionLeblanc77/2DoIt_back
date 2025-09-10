<?php

namespace App\Serializer;

use App\Entity\Section;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SectionNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $normalizedData = $this->normalizer->normalize($data, $format, $context);

        if (!isset($normalizedData['hasTasks']) || !is_array($normalizedData['hasTasks'])) {
            $normalizedData['tasks'] = [];
            return $normalizedData;
        }
        $normalizedData['tasks'] = array_map(function ($task) {
            return [
                'id' => $task['task']['id'],
                'content' => $task['task']['content'],
                'active' => $task['task']['active'],
                'position' => $task['position'],
                'users' => $task['task']['users'],
            ];
        }, $normalizedData['hasTasks']);
        unset($normalizedData['hasTasks']); 

        return $normalizedData;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Section
            && isset($context['groups']) 
            && in_array('section_with_tasks', $context['groups']);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Section::class => true,
        ];
    }
}