Feature: Routing

  Scenario: GET /resources
    Given I have OpenAPI specification file "routing.yaml"
    When I send a "GET" request to "/resources"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to the string "resourceCollection"

  Scenario: GET /resources/
    Given I have OpenAPI specification file "routing.yaml"
    When I send a "GET" request to "/resources/"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to the string "resourceCollection"

  Scenario: GET /resources/{resourceId}
    Given I have OpenAPI specification file "routing.yaml"
    When I send a "GET" request to "/resources/resourceId"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to the string "resourceItem"

  Scenario: GET /resources/{resourceId}/subresources
    Given I have OpenAPI specification file "routing.yaml"
    When I send a "GET" request to "/resources/resourceId/subresources"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to the string "subresourceCollection"

  Scenario: GET /resources/{resourceId}/subresources/{subresourceId}
    Given I have OpenAPI specification file "routing.yaml"
    When I send a "GET" request to "/resources/resourceId/subresources/subresourceId"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to the string "subresourceItem"

  Scenario: GET /integer-route/{id}
    Given I have OpenAPI specification file "routing.yaml"
    When I send a "GET" request to "/integer-route/123"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to the string "integerRouteItem"

  Scenario: GET /integer-route/
    Given I have OpenAPI specification file "routing.yaml"
    When I send a "GET" request to "/integer-route/"
    Then the response status code should be 404

  Scenario: GET /integer-route/{nonIntegerId}
    Given I have OpenAPI specification file "routing.yaml"
    When I send a "GET" request to "/integer-route/nonIntegerId"
    Then the response status code should be 404
