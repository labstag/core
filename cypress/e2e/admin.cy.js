describe('Admin CRUD', () => {
  beforeEach(() => {
    cy.visit(`https://${Cypress.env('SERVERNAME')}/admin`, { failOnStatusCode: false });
    cy.get('#login_username').clear().type('superadmin');
    cy.get('#login_password').clear().type('password');
    cy.get('form').submit();
    cy.url().should('include', '/admin');
  });

  it('liste des pages charge', () => {
    cy.contains('Pages').click();
    cy.url().should('include', 'Page');
    cy.get('table').should('be.visible');
  });

  it('crée une page', () => {
    cy.contains('Pages').click();
    cy.contains('Créer').click({ force: true });
    cy.get('input[name*="title"]').clear().type('Page Cypress');
    cy.contains('button', 'Enregistrer').click({ force: true });
    cy.contains('Page Cypress').should('exist');
  });
});