<?php

namespace App\Serializer;

use App\Entity\User;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserAttributeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'USER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }    

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param mixed       $object  Object to normalize
     * @param string|null $format  Format the normalization result will be encoded as
     * @param array       $context Context options for the normalizer
     *
     * @return array|string|int|float|bool|\ArrayObject|null \ArrayObject is used to make sure an empty object is encoded as an object not an array
     *
     * @throws InvalidArgumentException   Occurs when the object given is not a supported type for the normalizer
     * @throws CircularReferenceException Occurs when the normalizer detects a circular reference when no circular
     *                                    reference handler can fix it
     * @throws LogicException             Occurs when the normalizer is not called in an expected context
     * @throws ExceptionInterface         Occurs for all the other cases of errors
     */
    public function normalize(mixed $object,string $format = null, array $context = [])
    {       
        if ($this->tokenStorage->getToken()) {
            /**
             * @var User $user
             */
            $user = $this->tokenStorage->getToken()->getUser();
            
            if ($object instanceof User && $object->getUuid() === $user->getUuid()) {
                $context['groups'][] = 'user:extended:read';
            }
        }

        $context[self::ALREADY_CALLED] = true;

        return $this->normalizer->normalize($object, $format, $context);
    }    

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed       $data    Data to normalize
     * @param string|null $format  The format being (de-)serialized from or into
     * @param array       $context Context options for the normalizer
     *
     * @return bool
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = [])
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }
        return $data instanceof User;
    }
}