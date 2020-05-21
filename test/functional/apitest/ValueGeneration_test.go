package apitest

import (
	"net/http"
	"net/http/httptest"
	"swagger-mock/internal/di/config"
	"swagger-mock/pkg/assertjson"
)

func (suite *APISuite) TestValueGeneration_SpecificationWithAllPossibleSchemas_ExpectedValuesGenerated() {
	recorder := httptest.NewRecorder()
	handler := suite.createOpenAPIHandler(config.Configuration{
		SpecificationURL: "ValueGeneration.yaml",
	})

	request, _ := http.NewRequest("GET", "/content", nil)
	handler.ServeHTTP(recorder, request)

	suite.Equal(http.StatusOK, recorder.Code)
	suite.Equal("application/json; charset=utf-8", recorder.Header().Get("Content-Type"))
	suite.Equal("nosniff", recorder.Header().Get("X-Content-Type-Options"))
	assertjson.Has(suite.T(), recorder.Body.Bytes(), func(json *assertjson.AssertJSON) {
		json.Node("$.rangedInteger").ShouldBeANumberInRange(1, 5)
		json.Node("$.rangedFloat").ShouldBeANumberInRange(1, 1.5)
		json.Node("$.enum").ShouldMatch("^enumValue\\d$")
		json.Node("$.date").ShouldMatch("^\\d{4}-\\d{2}-\\d{2}$")
		json.Node("$.dateTime").ShouldMatch("^([0-9]+)-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])[Tt]([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\\.[0-9]+)?(([Zz])|([\\+|\\-]([01][0-9]|2[0-3]):[0-5][0-9]))$")
		json.Node("$.uuid").ShouldMatch("\\b[0-9a-f]{8}\\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\\b[0-9a-f]{12}\\b")
		json.Node("$.email").ShouldMatch("^\\S+@\\S+$")
		json.Node("$.uri").ShouldMatch("(http://|https://|www\\.)([^ '\"]*)")
		json.Node("$.hostname").ShouldMatch(".*\\..*")
		json.Node("$.ipv4").ShouldMatch("^(?:[0-9]{1,3}\\.){3}[0-9]{1,3}$")
		json.Node("$.ipv6").ShouldMatch("^[a-fA-F0-9:]+$")
		json.Node("$.byte").ShouldMatch("^[a-zA-Z0-9+\\/]+={0,2}$")
		json.Node("$.html").ShouldContain("<html lang=\"en\">")
		json.Node("$.html").ShouldMatch("<[^>]+>|&[^;]+;")
		json.Node("$.shortString").ShouldBeAStringWithLengthInRange(2, 4)
		json.Node("$.longString").ShouldBeAStringWithLengthInRange(100, 105)
		json.Node("$.array").ArrayShouldHaveElementsCount(5)
		json.Node("$.uniqueArrayOfObjects").ArrayShouldHaveElementsCount(1)
		json.Node("$.uniqueArrayOfObjects[0].key").EqualToTheString("value")
		json.Node("$.freeForm").ArrayShouldHaveElementsCount(1)
		json.Node("$.freeFormWithBooleanOption").ArrayShouldHaveElementsCount(1)
		json.Node("$.hashMap").ArrayShouldHaveElementsCount(1)
		json.Node("$.fixedHashMap").ArrayShouldHaveElementsCount(1)
		json.Node("$.fixedHashMap.default").EqualToTheString("value")
		json.Node("$.writeOnlyBoolean").ShouldNotExist()
		json.Node("$.writeOnlyInteger").ShouldNotExist()
		json.Node("$.writeOnlyNumber").ShouldNotExist()
		json.Node("$.writeOnlyString").ShouldNotExist()
		json.Node("$.writeOnlyArray").ShouldNotExist()
		json.Node("$.writeOnlyObject").ShouldNotExist()
	})
}
