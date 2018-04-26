Feature: Test donation page
  In order to see donation as a user
  I should be able to see my donation in my account profile

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |
      | LoadDonationData |

  Scenario: Be able to navigate in my donation page as an adherent with monthly donations
    Given I am logged as "jacques.picard@en-marche.fr"
    And I am on "/parametres/mon-compte"
    And I should see "jacques.picard@en-marche.fr"

    When I follow "Mes dons"
    Then I should be on "/parametres/mon-compte/mes-dons"
    And I should see "Votre dernier don a été fait"
    And I should see "50 €"
    And I should not see "Faire un nouveau don"
    And I should see "Arrêter mon don mensuel"

    When I follow "Arrêter mon don mensuel"
    Then I should be on "/parametres/mon-compte/mes-dons"

  Scenario: Be able to navigate in my donation page as an adherent without monthly donations
    Given I am logged as "michelle.dufour@example.ch"
    And I am on "/parametres/mon-compte"
    And I should see "michelle.dufour@example.ch"

    When I follow "Mes dons"
    Then I should be on "/parametres/mon-compte/mes-dons"
    And I should see "Votre dernier don a été fait"
    And I should see "50 €"
    And I should see "Faire un nouveau don"
    And I should not see "Arrêter mon don mensuel"

    When I follow "Faire un nouveau don"
    Then I should be on "/don"

  Scenario: Be able to navigate in my donation page as an adherent without donations
    Given I am logged as "carl999@example.fr"
    And I am on "/parametres/mon-compte"
    And I should see "carl999@example.fr"

    When I follow "Mes dons"
    Then I should be on "/parametres/mon-compte/mes-dons"
    And the response status code should be 200
    And I should not see "Votre dernier don a été fait"
    And I should not see "50 €"
    And I should not see "Faire un nouveau don"
