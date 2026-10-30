import { defineConfig, devices } from '@playwright/test';

/**
 * Tests E2E du calendrier de réservation (requêtes AJAX des créneaux).
 *
 * Depuis le dossier du plugin :   npx playwright test -c tests/e2e --project=chromium
 * URL de la page de réservation : E2E_BASE_URL=https://aquatonic.local/rennes/reservation-parcours/
 * Si aquatonic.local n'est pas dans le fichier hosts :  E2E_HOST_RESOLVER="MAP aquatonic.local 127.0.0.1" (Chromium).
 * (@playwright/test est résolu depuis le node_modules de la racine du projet WordPress.)
 */
export default defineConfig({
  testDir: '.',
  testMatch: '*.spec.ts',
  fullyParallel: false, // les tests comparent des disponibilités réelles : pas de réservations concurrentes
  reporter: 'list',
  use: {
    baseURL: process.env.E2E_BASE_URL || 'https://aquatonic.local/rennes/reservation-parcours/',
    ignoreHTTPSErrors: true,
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        launchOptions: process.env.E2E_HOST_RESOLVER ? { args: [`--host-resolver-rules=${process.env.E2E_HOST_RESOLVER}`] } : {},
      },
    },
    { name: 'mobile-safari', use: { ...devices['iPhone 13'] } },
  ],
});
