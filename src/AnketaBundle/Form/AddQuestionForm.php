<?php

namespace AnketaBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\CheckboxField;
use Symfony\Component\Form\TextField;
use Symfony\Component\Form\TextareaField;

class AddQuestionForm extends Form
{
    protected function configure()
    {
        $this->setDataClass('AnketaBundle\\Entity\\Question');
        $this->add(new TextField('question', array(
            'max_length' => 100,
        )));
        $this->add(new TextareaField('options'));
        $this->add(new CheckboxField('eval', array(
            'required' => false,
        )));
    }
}
