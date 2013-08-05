<?php
namespace Extia\Bundle\UserBundle\Form\Type;

use Extia\Bundle\UserBundle\Model\GroupPeer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Created by rhea.
 * @author lesmyrmidons <lesmyrmidons@gmail.com>
 * Date: 30/07/13
 * Time: 12:51
 */
class PersonType extends AbstractType
{
//    private $class;
//
//    public function __construct($class)
//    {
//        $this->class = $class;
//    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden')
            ->add('group', 'model', array ('class' => 'Extia\Bundle\USerBundle\Model\Group'))
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('email', 'email')
            ->add('phone', 'number')
            ->add('mobile', 'number')
            ->add('password', 'password')
            ->add('password_confirm', 'password');
    }

    /**
     * @param array $options
     *
     * @return array
     */
//    public function getDefaultOptions(array $options)
//    {
//        return array(
//            'data_class' => $this->class,
//        );
//    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'person_user';
    }
}
