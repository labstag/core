describe('Front public', () => {
  const pages = [
    '/',
    '/informations/contact',
    '/informations/plan-du-site',
    '/donnees-personnelles',
    '/posts',
    '/histoires',
  ];
  pages.forEach((p) => {
    it(`charge ${p}`, () => {
      cy.visit(`https://${Cypress.env('SERVERNAME')}${p}`, { failOnStatusCode: false });
      cy.get('body').should('be.visible');
    });
  });
});