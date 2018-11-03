Feature: Content negotiation

  Scenario: GET /content | Accept header is "application/json" | 200 json content returned
    Given I have OpenAPI specification file "content-negotiation.yaml"
    When I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/content"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should contain "application/json"
    And the JSON node "jsonString" should exist

  Scenario: GET /content | Accept header is "application/ld+json" | 200 ld+json content returned
    Given I have OpenAPI specification file "content-negotiation.yaml"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/content"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should contain "application/ld+json"
    And the JSON node "jsonLdString" should exist

  Scenario: GET /content | Accept header is "application/xml" | 200 xml content returned
    Given I have OpenAPI specification file "content-negotiation.yaml"
    When I add "Accept" header equal to "application/xml"
    And I send a "GET" request to "/content"
    Then the response status code should be 200
    And the response should be in XML
    And the header "Content-Type" should contain "application/xml"
    And the XML element "xmlString" should exist
