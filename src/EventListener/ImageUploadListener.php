<?php

namespace App\EventListener;

use App\Entity\Recipe;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Image upload listener.
 */
class ImageUploadListener
{
    private string $targetDir;

    /**
     * @param string $targetDir
     */
    public function __construct(string $targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * @param File $file
     *
     * @return string
     */
    public function upload(File $file): string
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        $file->move($this->targetDir, $fileName);

        return $fileName;
    }

    /**
     * @param PrePersistEventArgs $args
     *
     * @return void
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        $this->uploadFile($entity);
    }

    /**
     * @param PreUpdateEventArgs $args
     *
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        $this->uploadFile($entity);
    }

    /**
     * @param $entity
     *
     * @return void
     */
    private function uploadFile($entity): void
    {
        // upload only works for Recipe entities
        if (!$entity instanceof Recipe) {
            return;
        }

        $file = $entity->getImage();

        if (!$file instanceof File) {
            return;
        }

        $fileName = $this->upload($file);
        $entity->setImage($fileName);
    }
}
