Feature: Value generation

  Scenario: GET /content | no parameters | 200 entity returned
    Given I have OpenAPI specification file "value-generation.yaml"
    When I send a "GET" request to "/content"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "rangedInteger" should be a number in range between "1" and "5"
    And the JSON node "rangedFloat" should be a number in range between "1" and "1.5"
    And the JSON node "enum" should match "/^enumValue\d$/"
    And the JSON node "date" should match format "%d-%d-%d"
    And the JSON node "dateTime" should match format "%d-%d-%dT%d:%d:%d%s"
    And the JSON node "uuid" should match "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/"
    And the JSON node "email" should match "/^.+\@.+\..+$/"
    And the JSON node "uri" should match "/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/"
    And the JSON node "hostname" should match "/^([\da-z\.-]+)\.([a-z\.]{2,6})$/"
    And the JSON node "ipv4" should match format "%d.%d.%d.%d"
    And the JSON node "ipv6" should match "/^[a-fA-F0-9:]+$/"
    And the JSON node "byte" should match "/^[a-zA-Z0-9+\/]+={0,2}$/"
    And the JSON node "shortString" should be a string with length in range between "2" and "4"
    And the JSON node "longString" should be a string with length in range between "100" and "105"
