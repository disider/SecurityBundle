<?php

namespace Diside\SecurityBundle\Tests\DependencyInjection;

use Diside\SecurityBundle\Tests\ServiceTestCase;

class ServicesTest extends ServiceTestCase
{

    /**
     * @test
     */
    public function testFormProcessors()
    {
        $this->assertService('company_form_processor', 'Diside\SecurityBundle\Form\Processor\CompanyFormProcessor');
        $this->assertService('user_form_processor', 'Diside\SecurityBundle\Form\Processor\UserFormProcessor');
        $this->assertService('registration_form_processor', 'Diside\SecurityBundle\Form\Processor\RegistrationFormProcessor');
        $this->assertService('request_reset_password_form_processor', 'Diside\SecurityBundle\Form\Processor\RequestResetPasswordFormProcessor');
        $this->assertService('reset_password_form_processor', 'Diside\SecurityBundle\Form\Processor\ResetPasswordFormProcessor');
        $this->assertService('change_password_form_processor', 'Diside\SecurityBundle\Form\Processor\ChangePasswordFormProcessor');
    }

    /**
     * @test
     */
    public function testMailer()
    {
        $this->assertService('security.mailer', 'Diside\SecurityBundle\Mailer\DefaultMailer');
    }
}
