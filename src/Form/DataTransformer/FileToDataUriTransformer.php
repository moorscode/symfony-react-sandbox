<?php

namespace App\Form\DataTransformer;

use League\Uri\Components\DataPath;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Converts a file to data URI.
 */
class FileToDataUriTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value instanceof \SplFileInfo) {
            return '';
        }
        // todo: what should this do?
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value): ?File
    {
        if (empty($value)) {
            return null;
        }

        return $this->storeTemporary($value);
    }

    /**
     * @param string $data
     *
     * @return File
     */
    public function storeTemporary(string $data): File
    {
        $prefix = 'data:';
        if (str_starts_with($data, $prefix)) {
            $data = substr($data, strlen($prefix));
        }

        $dataPath = DataPath::new($data);
        if (false === $path = tempnam($directory = sys_get_temp_dir(), 'Base64EncodedFile')) {
            throw new FileException(sprintf('Unable to create a file into the "%s" directory', $directory));
        }
        $dataPath->save($path, 'w');

        return new File($path);
    }
}
