<?php
/**
 * Mailer Helper
 * Schedulr — helpers/Mailer.php
 *
 * Wraps PHPMailer to send Schedulr emails.
 * Require config/email.php before using this.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/email.php';

class Mailer {

    /**
     * Send the email verification link to the student.
     *
     * @param  string $toEmail   Student's email address
     * @param  string $toName    Student's display name
     * @param  string $token     The verification token from course_registrations
     * @param  int    $regId     The registration_id
     * @return bool   true on success, false on failure
     */
    public static function sendVerification(string $toEmail, string $toName, string $token, int $regId): bool {
        $verifyUrl = APP_BASE_URL . '/api/verify-email.php?token=' . urlencode($token) . '&id=' . $regId;

        $subject = 'Verify your Schedulr registration';

        $body = self::verificationEmailHtml($toName, $verifyUrl);
        $altBody = self::verificationEmailText($toName, $verifyUrl);

        return self::send($toEmail, $toName, $subject, $body, $altBody);
    }

    /**
     * Resend a verification email (same as sendVerification — kept separate
     * so you can customise the subject line if needed).
     */
    public static function resendVerification(string $toEmail, string $toName, string $token, int $regId): bool {
        $verifyUrl = APP_BASE_URL . '/api/verify-email.php?token=' . urlencode($token) . '&id=' . $regId;

        $subject = 'Schedulr — resent verification link';
        $body    = self::verificationEmailHtml($toName, $verifyUrl, resent: true);
        $altBody = self::verificationEmailText($toName, $verifyUrl, resent: true);

        return self::send($toEmail, $toName, $subject, $body, $altBody);
    }

    /**
     * Low-level send method — configures PHPMailer and dispatches the email.
     */
    private static function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $plainBody): bool {
        $mail = new PHPMailer(true); // true = enable exceptions

        try {
            // ── Server settings ──────────────────────────────────────────────
            // Uncomment the next line to see detailed SMTP debug output:
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;

            // ── Who it's from ────────────────────────────────────────────────
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addReplyTo(MAIL_FROM, MAIL_FROM_NAME);

            // ── Who it goes to ───────────────────────────────────────────────
            $mail->addAddress($toEmail, $toName);

            // ── Content ──────────────────────────────────────────────────────
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $plainBody;  // Plain-text fallback

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mailer error sending to {$toEmail}: " . $mail->ErrorInfo);
            return false;
        }
    }

    // ── Email templates ───────────────────────────────────────────────────────

    private static function verificationEmailHtml(string $name, string $url, bool $resent = false): string {
        $firstName = explode(' ', trim($name))[0];
        $resentNote = $resent ? '<p style="color:#6b7280;font-size:13px;">You requested this email to be resent.</p>' : '';

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center" style="padding:40px 20px;">
        <table width="600" cellpadding="0" cellspacing="0"
               style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#dc2626,#991b1b);padding:32px 40px;text-align:center;">
              <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:10px;padding:10px 20px;">
                <span style="color:#ffffff;font-size:28px;font-weight:800;letter-spacing:2px;">SCHEDULR</span>
              </div>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px 40px 32px;">
              <h2 style="margin:0 0 8px;color:#1a1a1a;font-size:22px;">Hi {$firstName},</h2>
              <p style="color:#374151;font-size:15px;line-height:1.6;margin:0 0 24px;">
                {$resentNote}
                Thanks for submitting your course registration through Schedulr.
                Please verify your email address so we can start processing your request.
              </p>

              <!-- CTA Button -->
              <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                <tr>
                  <td style="background:#dc2626;border-radius:8px;padding:14px 32px;">
                    <a href="{$url}"
                       style="color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;display:block;">
                      ✓ &nbsp;Verify My Email
                    </a>
                  </td>
                </tr>
              </table>

              <p style="color:#6b7280;font-size:13px;line-height:1.5;margin:0 0 8px;">
                Button not working? Copy and paste this link into your browser:
              </p>
              <p style="font-size:12px;word-break:break-all;margin:0 0 24px;">
                <a href="{$url}" style="color:#dc2626;">{$url}</a>
              </p>

              <hr style="border:none;border-top:1px solid #e5e7eb;margin:0 0 24px;">

              <p style="color:#6b7280;font-size:13px;line-height:1.5;margin:0;">
                This link expires in <strong>24 hours</strong>. If you didn't submit a registration, you can safely ignore this email.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f9fafb;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;">
              <p style="margin:0;color:#9ca3af;font-size:12px;">
                Schedulr &mdash; Course Registration Assistant &middot; Ashesi University
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    private static function verificationEmailText(string $name, string $url, bool $resent = false): string {
        $firstName = explode(' ', trim($name))[0];
        $resentNote = $resent ? "You requested this email to be resent.\n\n" : "";
        return <<<TEXT
Hi {$firstName},

{$resentNote}Thanks for submitting your course registration through Schedulr.

Please verify your email address by visiting the link below:
{$url}

This link expires in 24 hours.

If you didn't submit a registration, you can safely ignore this email.

— Schedulr, Ashesi University
TEXT;
    }
}