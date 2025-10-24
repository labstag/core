describe('template spec', () => {
  it('passes', () => {
    cy.visit(`https://${Cypress.env('SERVERNAME')}`, {failOnStatusCode: false});
    cy.screenshot('first-page');
  });

  it('redirection /admin vers /login et connexion superadmin', () => {
    cy.visit(`https://${Cypress.env('SERVERNAME')}/admin`, {failOnStatusCode: false});
    cy.url().should('include', '/login');
    cy.get('input[name="_username"], input[name="username"], input[type="text"]').first().type('superadmin');
    cy.get('input[name="_password"], input[name="password"], input[type="password"]').first().type('password');
    cy.get('form').submit();
    cy.url().should('not.include', '/login');
    cy.visit(`https://${Cypress.env('SERVERNAME')}/admin`, {failOnStatusCode: false});
    cy.url().should('include', '/admin');
    cy.screenshot('apres-connexion-superadmin');
  });
  it('vérification des pages principales', () => {
    const pages = [
      '/informations/contact',
      '/informations/plan-du-site',
      '/donnees-personnelles',
      '/posts',
      '/mes-etoiles-github',
      '/mes-derniers-films-vus',
      '/histoires'
    ];
    pages.forEach((page) => {
      cy.visit(`https://${Cypress.env('SERVERNAME')}${page}`, {failOnStatusCode: false});
      cy.url().should('include', page === '/' ? Cypress.env('SERVERNAME') : page);
      cy.screenshot(`page-${page.replace('/', '')}`);
    });
  });
})