<?php
namespace Niyanta\Controllers;

use Niyanta\Core\Branding;
use Niyanta\Core\Flash;
use Niyanta\Core\Log;
use Niyanta\Core\View;

class BrandingController
{
    public function index(): void
    {
        View::render('settings.branding', [
            'company'  => Branding::companyName(),
            'logo'     => Branding::logoUrl(),
            'palettes' => Branding::palettes(),
            'active'   => Branding::activePalette(),
        ], 'app');
    }

    public function update(): void
    {
        $company = trim((string) request('company_name', ''));
        if ($company !== '') {
            Branding::set('company_name', $company);
        }

        if (!empty($_FILES['logo']['name']) && ($_FILES['logo']['error'] ?? 1) === UPLOAD_ERR_OK) {
            $this->handleLogoUpload($_FILES['logo']);
        }

        Log::record('branding.update', 'Updated company branding');
        Flash::success('Branding updated.');
        redirect('/settings/branding');
    }

    public function createPalette(): void
    {
        $name = trim((string) request('name', 'Custom'));
        $primary = (string) request('primary', '#0B1F4D');
        $secondary = (string) request('secondary', '#6EC1FF');
        $accent = (string) request('accent', '#FFD84D');
        Branding::createPalette($name ?: 'Custom', $primary, $secondary, $accent);
        Flash::success('Palette created.');
        redirect('/settings/branding');
    }

    public function activatePalette(): void
    {
        Branding::setActivePalette((int) request('id', 0));
        Log::record('branding.palette', 'Switched active palette');
        Flash::success('Active palette updated.');
        redirect('/settings/branding');
    }

    public function deletePalette(): void
    {
        Branding::deletePalette((int) request('id', 0));
        Flash::success('Palette removed.');
        redirect('/settings/branding');
    }

    private function handleLogoUpload(array $file): void
    {
        $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
        $mime = mime_content_type($file['tmp_name']) ?: '';
        if (!isset($allowed[$mime])) {
            Flash::error('Logo must be a PNG, JPG, WEBP or SVG image.');
            redirect('/settings/branding');
        }
        $dir = base_path('uploads/logos');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $filename = 'logo_' . time() . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            Flash::error('Could not save the uploaded logo.');
            redirect('/settings/branding');
        }
        Branding::set('logo', $filename);
    }
}
