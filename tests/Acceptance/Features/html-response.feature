Feature: Html response

  Scenario: GET /html | no parameters | 200 html page returned
    Given I have OpenAPI specification file "html-response.yaml"
    When I send a "GET" request to "/html"
    Then the response status code should be 200
    And I should see an "html" element
