import { test, expect, Page } from '@playwright/test';

/**
 * Validation du créneau à la soumission du formulaire : un créneau que le calendrier ne propose pas
 * (ici une heure modifiée à la main) est refusé par le serveur, un créneau proposé passe.
 * Aucune réservation n'est créée : on s'arrête à la validation de la page du calendrier.
 */

const PARTICIPANTS = '2';
const NEXT_BUTTON = '.gform_next_button';
const SLOTS_CONTAINER = '#tmsm-aquatonic-course-slots-container';
const DATE_INPUT = '.tmsm-aquatonic-course-date input';
const HOUR_INPUT = '.tmsm-aquatonic-course-hourminutes .gfield_time_hour input';
const MINUTES_INPUT = '.tmsm-aquatonic-course-hourminutes .gfield_time_minute input';
const UNAVAILABLE_MESSAGE = /plus disponible|no longer available/i;

// Heure qui n'est jamais un créneau (les créneaux tombent sur des multiples de 10, 15, 20 ou 30 minutes).
const NOT_A_SLOT = { hour: '03', minutes: '07' };

async function openCalendar(page: Page) {
  await page.goto('./');
  const input = page.locator('.tmsm-aquatonic-course-participants input');
  await input.waitFor({ state: 'visible', timeout: 10000 });
  await input.fill(PARTICIPANTS);
  // Quitter le champ (comme un visiteur) déclenche "change" : GravityForms n'affiche le bouton « Continuer »
  // que lorsque les participants sont renseignés (logique conditionnelle sur ce champ).
  await input.press('Tab');
  await expect(page.locator('#tmsm-inline-calendar')).toBeVisible({ timeout: 15000 });
  await expect(page.locator('.tmsm-day-col')).toHaveCount(7);
  await expect(page.locator('.tmsm-day-spinner:visible')).toHaveCount(0, { timeout: 20000 });
}

/** Choisit le premier créneau proposé de la semaine affichée, comme un clic dans la liste d'un jour. */
async function pickFirstOfferedSlot(page: Page): Promise<{ date: string; hourminutes: string }> {
  const slot = await page.evaluate(() => {
    const selects = Array.from(document.querySelectorAll<HTMLSelectElement>('.tmsm-day-col select'));
    for (const select of selects) {
      const option = Array.from(select.options).find((o) => o.dataset.hourminutes);
      if (option) {
        select.value = option.value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        return { date: option.dataset.date as string, hourminutes: option.dataset.hourminutes as string };
      }
    }
    return null;
  });
  expect(slot, 'aucun créneau proposé sur la semaine affichée').not.toBeNull();
  return slot as { date: string; hourminutes: string };
}

/** Remplace l'heure choisie dans les champs du formulaire, sans passer par le calendrier. */
const tamperTime = (page: Page, hour: string, minutes: string) =>
  page.evaluate(
    ({ h, m, hourInput, minutesInput }) => {
      const $ = (window as any).jQuery;
      $(hourInput).prop('disabled', false).val(h);
      $(minutesInput).prop('disabled', false).val(m);
    },
    { h: hour, m: minutes, hourInput: HOUR_INPUT, minutesInput: MINUTES_INPUT }
  );

test.describe('Validation du créneau à la soumission', () => {
  test('un créneau proposé par le calendrier passe la validation', async ({ page }) => {
    await openCalendar(page);
    const slot = await pickFirstOfferedSlot(page);
    await expect(page.locator(DATE_INPUT)).toHaveValue(slot.date);

    await page.locator(NEXT_BUTTON).first().click();

    // La page du calendrier est quittée : la validation a accepté le créneau.
    await expect(page.locator(SLOTS_CONTAINER)).toBeHidden({ timeout: 20000 });
    await expect(page.getByText(UNAVAILABLE_MESSAGE)).toHaveCount(0);
  });

  test("une heure qui n'est pas un créneau est refusée avec un message", async ({ page }) => {
    await openCalendar(page);
    await pickFirstOfferedSlot(page);
    await tamperTime(page, NOT_A_SLOT.hour, NOT_A_SLOT.minutes);

    await page.locator(NEXT_BUTTON).first().click();

    // Le formulaire reste sur la page du calendrier, avec le champ heure en erreur.
    const timeField = page.locator('.tmsm-aquatonic-course-hourminutes');
    await expect(timeField).toHaveClass(/gfield_error/, { timeout: 20000 });
    await expect(timeField.locator('.gfield_description')).toContainText(UNAVAILABLE_MESSAGE);
    await expect(page.locator(SLOTS_CONTAINER)).toBeVisible();
  });
});
