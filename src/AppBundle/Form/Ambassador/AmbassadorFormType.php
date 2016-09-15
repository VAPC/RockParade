<?php

namespace AppBundle\Form\Ambassador;

use AppBundle\Form\AbstractFormType;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Form\Validation as EntityAssert;

/**
 * @author Vehsamrak
 */
class AmbassadorFormType extends AbstractFormType
{

    /**
     * @var string
     * @Assert\NotBlank(message="Parameter is mandatory: name.")
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank(message="Parameter is mandatory: description.")
     */
    public $description;

    /**
     * @Assert\All({
     *     @Assert\Collection(
     *     fields = {
     *         "login" = {
     *             @EntityAssert\EntityExists(entityClass="AppBundle\Entity\User", entityField="login"),
     *             @Assert\NotBlank
     *         },
     *         "short_description" = {
     *             @Assert\NotBlank
     *         }
     *     })
     * })
     */
    public $members;
}
