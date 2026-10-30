import { test, expect, Page, Request } from '@playwright/test';

/**
 * Calendrier de réservation : les créneaux d'une semaine doivent venir d'UNE requête AJAX,
 * et être identiques à ceux de l'ancien endpoint (une requête par jour).
 */

const WEEK_ACTION = 'tmsm-aquatonic-course-booking-times-week';
const DAY_ACTION = 'tmsm-aquatonic-course-booking-times';
const PARTICIPANTS = '2';

type Slot = { date: string; hourminutes: string | null; hour: string | null; minutes: string | null; capacity?: number; index?: number; message?: string };

/** Enregistre les actions AJAX de créneaux envoyées par la page (action -> nombre d'appels, et paramètres). */
function trackTimesRequests(page: Page) {
  const calls: { action: string; params: URLSearchParams }[] = [];
  page.on('request', (req: Request) => {
    if (req.method() !== 'POST' || !req.url().includes('admin-ajax.php')) return;
    const params = new URLSearchParams(req.postData() || '');
    const action = params.get('action') || '';
    if (action === WEEK_ACTION || action === DAY_ACTION) calls.push({ action, params });
  });
  return {
    calls,
    count: (action: string) => calls.filter((c) => c.action === action).length,
    reset: () => calls.splice(0, calls.length),
  };
}

async function openCalendar(page: Page, participants = PARTICIPANTS) {
  await page.goto('./');
  const input = page.locator('.tmsm-aquatonic-course-participants input');
  await input.waitFor({ state: 'visible', timeout: 10000 });
  await input.fill(participants);
  await waitForWeekLoaded(page);
}

async function waitForWeekLoaded(page: Page) {
  await expect(page.locator('#tmsm-inline-calendar')).toBeVisible({ timeout: 15000 });
  await expect(page.locator('.tmsm-day-col')).toHaveCount(7);
  await expect(page.locator('.tmsm-day-spinner:visible')).toHaveCount(0, { timeout: 20000 });
}

/** Appelle un endpoint AJAX du plugin depuis la page (même session, même nonce que le calendrier). */
function callEndpoint<T>(page: Page, params: Record<string, string>): Promise<T> {
  return page.evaluate(async (p) => {
    const app = (window as any).TmsmAquatonicCourseApp;
    const body = new URLSearchParams({ ...p, nonce: app.data.nonce });
    const res = await fetch(app.data.ajaxurl, { method: 'POST', body, credentials: 'same-origin' });
    return res.json();
  }, params) as Promise<T>;
}

const displayedDates = (page: Page) => page.locator('.tmsm-day-col').evaluateAll((cols) => cols.map((c) => (c as HTMLElement).dataset.date as string));

/** Créneaux réellement affichés dans le select de chaque jour. */
async function displayedTimes(page: Page): Promise<Record<string, string[]>> {
  return page.locator('.tmsm-day-col').evaluateAll((cols) =>
    Object.fromEntries(
      cols.map((col) => [
        (col as HTMLElement).dataset.date as string,
        Array.from(col.querySelectorAll('select option[data-hourminutes]')).map((o) => (o as HTMLElement).dataset.hourminutes as string),
      ])
    )
  );
}

const onlyTimes = (slots: Slot[]) => slots.filter((s) => s.hourminutes).map((s) => s.hourminutes as string);

/** Choisit une date dans le calendrier (même événement "changeDate" qu'un clic sur un jour). */
const selectDate = (page: Page, isoDate: string) =>
  page.evaluate((d) => (window as any).jQuery('#tmsm-inline-calendar').datepicker('setDate', new Date(`${d}T12:00:00`)), isoDate);

/** Marque les colonnes actuelles : si la grille est reconstruite, la marque disparaît. */
const markColumns = (page: Page) => page.locator('.tmsm-day-col').evaluateAll((cols) => cols.forEach((c) => ((c as HTMLElement).dataset.marker = 'old')));

const addDays = (isoDate: string, days: number) => {
  const d = new Date(`${isoDate}T12:00:00Z`);
  d.setUTCDate(d.getUTCDate() + days);
  return d.toISOString().slice(0, 10);
};

test.describe('Calendrier : requêtes AJAX des créneaux', () => {
  test('la semaine est chargée avec une seule requête (et plus sept)', async ({ page }) => {
    const tracker = trackTimesRequests(page);
    await openCalendar(page);

    expect(tracker.count(WEEK_ACTION)).toBe(1);
    expect(tracker.count(DAY_ACTION)).toBe(0);

    const params = tracker.calls[0].params;
    expect(params.get('participants')).toBe(PARTICIPANTS);
    expect(params.get('days')).toBe('7');
    expect(params.get('date_from')).toBe((await displayedDates(page))[0]);
  });

  test('choisir un autre jour de la même semaine ne relance aucune requête', async ({ page }) => {
    const tracker = trackTimesRequests(page);
    await openCalendar(page);
    const dates = await displayedDates(page);
    const before = await displayedTimes(page);
    await markColumns(page);
    tracker.reset();

    await selectDate(page, dates[6]);
    // La grille est reconstruite (donc le calendrier a bien réagi) mais sans appel réseau.
    await expect(page.locator('.tmsm-day-col').first()).not.toHaveAttribute('data-marker', 'old');
    await waitForWeekLoaded(page);

    expect(tracker.calls).toHaveLength(0);
    expect(await displayedDates(page)).toEqual(dates);
    expect(await displayedTimes(page)).toEqual(before);
  });

  test('changer de semaine déclenche une seule requête', async ({ page }) => {
    const tracker = trackTimesRequests(page);
    await openCalendar(page);
    const firstWeek = (await displayedDates(page))[0];
    tracker.reset();

    await selectDate(page, addDays(firstWeek, 7));
    await expect(page.locator('.tmsm-day-col').first()).toHaveAttribute('data-date', addDays(firstWeek, 7));
    await waitForWeekLoaded(page);

    expect((await displayedDates(page))[0]).toBe(addDays(firstWeek, 7));
    expect(tracker.count(WEEK_ACTION)).toBe(1);
    expect(tracker.count(DAY_ACTION)).toBe(0);
  });

  test('changer le nombre de participants recharge la semaine avec une seule requête', async ({ page }) => {
    const tracker = trackTimesRequests(page);
    await openCalendar(page, '2');
    tracker.reset();

    await page.locator('.tmsm-aquatonic-course-participants input').fill('3');
    await waitForWeekLoaded(page);
    await expect.poll(() => tracker.count(WEEK_ACTION)).toBe(1);

    expect(tracker.calls[0].params.get('participants')).toBe('3');
  });
});

test.describe('Calendrier : résultats identiques à l\'ancien endpoint', () => {
  for (const participants of ['1', '2', '4']) {
    test(`l'endpoint "semaine" renvoie exactement les 7 réponses de l'ancien endpoint (${participants} pers.)`, async ({ page }) => {
      await openCalendar(page, participants);
      const dates = await displayedDates(page);

      const fetchWeek = () => callEndpoint<Record<string, Slot[]>>(page, {
        action: WEEK_ACTION, date_from: dates[0], days: '7', participants,
      });
      // Les créneaux du jour disparaissent au fil de l'heure (délai minimum avant réservation) :
      // on encadre les appels de l'ancien endpoint par deux appels "semaine". Le temps ne fait
      // qu'avancer, donc l'ancien résultat doit être égal à l'un des deux.
      const weekBefore = await fetchWeek();
      const perDay: Record<string, Slot[]> = {};
      for (const date of dates) {
        perDay[date] = await callEndpoint<Slot[]>(page, { action: DAY_ACTION, date, participants });
      }
      const weekAfter = await fetchWeek();

      expect(Object.keys(weekBefore)).toEqual(dates);
      const matchesOne = JSON.stringify(perDay) === JSON.stringify(weekBefore) || JSON.stringify(perDay) === JSON.stringify(weekAfter);
      if (!matchesOne) {
        expect(weekAfter).toEqual(perDay); // fait échouer le test avec un diff lisible
      }
    });

    test(`les créneaux affichés dans les 7 listes sont ceux de l'ancien endpoint (${participants} pers.)`, async ({ page }) => {
      await openCalendar(page, participants);
      const dates = await displayedDates(page);
      const displayed = await displayedTimes(page);

      const today = await page.evaluate(() => (window as any).moment().format('YYYY-MM-DD'));

      for (const date of dates) {
        const expected = onlyTimes(await callEndpoint<Slot[]>(page, { action: DAY_ACTION, date, participants }));
        // Aujourd'hui, les créneaux les plus proches ont pu devenir trop tardifs depuis le chargement
        // de la page : on tolère des créneaux affichés en plus au DÉBUT de la liste, jamais ailleurs.
        const actual = date === today ? displayed[date].slice(Math.max(0, displayed[date].length - expected.length)) : displayed[date];
        expect(actual, `créneaux affichés le ${date}`).toEqual(expected);
      }
    });
  }

  test('une date invalide est refusée par l\'endpoint "semaine" sans casser la page', async ({ page }) => {
    await openCalendar(page);
    const status = await page.evaluate(async () => {
      const app = (window as any).TmsmAquatonicCourseApp;
      const body = new URLSearchParams({ action: 'tmsm-aquatonic-course-booking-times-week', nonce: app.data.nonce, date_from: '31/12/2030', days: '7', participants: '2' });
      return (await fetch(app.data.ajaxurl, { method: 'POST', body })).status;
    });
    expect(status).toBe(400);
  });
});
