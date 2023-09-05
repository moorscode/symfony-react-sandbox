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
                    'liform' => ['description' => 'To display server-side validation and to avoid summoning obnoxious ghosts, Beetlejuice is an invalid value for this field.'],
                ]
            )
            ->add('description', Type\TextType::class, ['label' => 'Description', 'liform' => ['widget' => 'textarea']])
            ->add('image', Type\TextType::class, ['label' => 'Image', 'liform' => ['widget' => 'file']]);

        $builder->get('image')->addViewTransformer(new FileToDataUriTransformer());
    }
}
