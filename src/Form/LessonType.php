<?php

namespace App\Form;

use App\Entity\Lesson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class LessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Название'])
            ->add('target', TextType::class, ['label' => 'Цель'])
            ->add('task', TextType::class, ['label' => 'Конечный результат'])
            ->add('instrumentation', TextType::class, ['label' => 'Инструменты и матеиалы'])
            ->add('theory', TextareaType::class, [
                'label' => 'Теоретический материал',
                'required' => false,
                ])
            ->add('video', TextareaType::class, ['label' => 'Видеоурок', 'required' => false,])
            ->add('test', TextType::class, ['label' => 'Пройти тест'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}