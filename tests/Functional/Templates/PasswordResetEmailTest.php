<?php

namespace Poncho\AdminBundle\Tests\Functional\Templates;

use Poncho\AdminBundle\Tests\App\Entity\AdminUser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/**
 * Regression test: a user's own name reaches the password-reset e-mail HTML
 * through trans() placeholders, which are not escaped by Twig's autoescape
 * (the whole message is marked |raw because it legitimately contains markup).
 * An admin able to edit names could otherwise inject HTML/links into a
 * DIFFERENT user's reset e-mail.
 */
class PasswordResetEmailTest extends KernelTestCase
{
    public function testUserNameIsEscapedInTheResetEmail(): void
    {
        self::bootKernel();

        $user = new AdminUser();
        $user->firstname = '<script>alert(1)</script>';
        $user->lastname = 'Evil<img src=x onerror=alert(2)>';
        $user->email = 'evil@example.test';

        $twig = self::getContainer()->get(Environment::class);
        $html = $twig->render('@PonchoAdmin/email/password_reset.html.twig', [
            'user' => $user,
            'token' => 'abc123',
        ]);

        $this->assertStringNotContainsString('<script>', $html, 'The name must not inject a raw <script> tag.');
        $this->assertStringNotContainsString('<img src=x', $html, 'The name must not inject a raw, unescaped <img> tag.');
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html, 'The name must still appear, escaped, in the e-mail.');
        $this->assertStringContainsString('&lt;img src=x onerror=alert(2)&gt;', $html, 'The rest of the name must appear, escaped, as inert text.');

        // The surrounding markup the translation legitimately carries must still render as HTML.
        $this->assertStringContainsString('<a href=', $html, 'The reset link must still be real HTML, not escaped along with the name.');
    }
}
