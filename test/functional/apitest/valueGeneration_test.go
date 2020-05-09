package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/pkg/jsonassert"
)

func (suite *APISuite) TestValueGeneration_SpecificationWithAllPossibleSchemas_ExpectedValuesGenerated() {
	recorder := httptest.NewRecorder()
	request, _ := http.NewRequest("GET", "/content", nil)
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "value-generation.yaml",
	})

	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json", recorder.Header().Get("Content-Type"))
	json := jsonassert.MustParse(suite.T(), recorder.Body.Bytes())
	json.AssertNodeShouldBeANumberInRange("$.rangedInteger", 1, 5)
	json.AssertNodeShouldBeANumberInRange("$.rangedFloat", 1, 1.5)
	json.AssertNodeShouldMatch("$.enum", "^enumValue\\d$")
	json.AssertNodeShouldMatch("$.date", "^\\d{4}-\\d{2}-\\d{2}$")
	json.AssertNodeShouldMatch("$.dateTime", "^([0-9]+)-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])[Tt]([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\\.[0-9]+)?(([Zz])|([\\+|\\-]([01][0-9]|2[0-3]):[0-5][0-9]))$")
	json.AssertNodeShouldMatch("$.uuid", "\\b[0-9a-f]{8}\\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\\b[0-9a-f]{12}\\b")
	json.AssertNodeShouldMatch("$.email", "^\\S+@\\S+$")
	json.AssertNodeShouldMatch("$.uri", "(http://|https://|www\\.)([^ '\"]*)")
	json.AssertNodeShouldMatch("$.hostname", ".*\\..*")
	json.AssertNodeShouldMatch("$.ipv4", "^(?:[0-9]{1,3}\\.){3}[0-9]{1,3}$")
	json.AssertNodeShouldMatch("$.ipv6", "^[a-fA-F0-9:]+$")
	json.AssertNodeShouldMatch("$.byte", "^[a-zA-Z0-9+\\/]+={0,2}$")
	json.AssertNodeShouldContain("$.html", "<html lang=\"en\">")
	json.AssertNodeShouldMatch("$.html", "<[^>]+>|&[^;]+;")
	json.AssertNodeShouldBeAStringWithLengthInRange("$.shortString", 2, 4)
	json.AssertNodeShouldBeAStringWithLengthInRange("$.longString", 100, 105)
	json.AssertArrayNodeShouldHaveElementsCount("$.array", 5)
	json.AssertArrayNodeShouldHaveElementsCount("$.uniqueArrayOfObjects", 1)
	json.AssertNodeEqualToTheString("$.uniqueArrayOfObjects[0].key", "value")
}
