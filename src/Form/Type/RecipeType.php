<?php

namespace App\Form\Type;

use App\Form\DataTransformer\FileToDataUriTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The recipe form type.
 */
class RecipeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                Type\TextType::class,
                [
                    'label' => 'Name',
                    'required' => true,
                    'attr' => ['placeholder' => 'Some name'],
                ]
            )
            ->add('description', Type\TextType::class, ['label' => 'Description'])
            ->add('image', Type\TextType::class, ['label' => 'Image']);

        $builder->get('image')->addViewTransformer(new FileToDataUriTransformer());
    }
}
