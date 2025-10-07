describe('Navigation Tests', () => {
  const baseUrl = 'https://labstag.traefik.me';

  it('should visit homepage and navigate to admin', () => {
    // Visite de la page d'accueil
    cy.visit(baseUrl, { failOnStatusCode: false });
    
    // Vérification que la page d'accueil s'est chargée
    cy.url().should('eq', baseUrl + '/');
    
    // Prendre une capture d'écran de la page d'accueil
    cy.screenshot('homepage');
    
    // Attendre que la page soit complètement chargée
    cy.wait(2000);
    
    // Navigation vers la page d'administration
    cy.visit(baseUrl + '/admin', { failOnStatusCode: false });
    
    // Vérification que nous sommes sur la page admin
    cy.url().should('include', '/admin');
    
    // Prendre une capture d'écran de la page admin
    cy.screenshot('admin-page');
    
    // Optionnel : vérifier la présence d'éléments spécifiques sur la page admin
    // cy.contains('Admin').should('be.visible');
  });

  it('should visit homepage only', () => {
    // Test simple pour vérifier que la page d'accueil fonctionne
    cy.visit(baseUrl, { failOnStatusCode: false });
    cy.screenshot('homepage-test');
  });
})