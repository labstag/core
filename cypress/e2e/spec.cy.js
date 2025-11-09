describe('template spec', () => {
  it('passes', () => {
    cy.visit(`https://${Cypress.env('SERVERNAME')}`, {failOnStatusCode: false});
    cy.screenshot('first-page');
  });

  it('redirection /admin vers /connexion et connexion superadmin', () => {
    cy.visit(`https://${Cypress.env('SERVERNAME')}/admin`, {failOnStatusCode: false});
    cy.url().should('include', '/connexion');
    cy.get('#login_username').should('be.visible').clear().type('superadmin');
    cy.get('#login_password').should('be.visible').clear().type('password');
    cy.get('form').submit();
    cy.url().should('not.include', '/connexion');
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