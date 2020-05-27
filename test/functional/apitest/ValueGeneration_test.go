package apitest

import (
	"github.com/muonsoft/api-testing/assertjson"
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"net/http"
	"net/http/httptest"
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
		json.Node("$.rangedInteger").IsNumberInRange(1, 5)
		json.Node("$.rangedFloat").IsNumberInRange(1, 1.5)
		json.Node("$.enum").Matches("^enumValue\\d$")
		json.Node("$.date").Matches("^\\d{4}-\\d{2}-\\d{2}$")
		json.Node("$.dateTime").Matches("^([0-9]+)-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])[Tt]([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\\.[0-9]+)?(([Zz])|([\\+|\\-]([01][0-9]|2[0-3]):[0-5][0-9]))$")
		json.Node("$.uuid").Matches("\\b[0-9a-f]{8}\\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\\b[0-9a-f]{12}\\b")
		json.Node("$.email").Matches("^\\S+@\\S+$")
		json.Node("$.uri").Matches("(http://|https://|www\\.)([^ '\"]*)")
		json.Node("$.hostname").Matches(".*\\..*")
		json.Node("$.ipv4").Matches("^(?:[0-9]{1,3}\\.){3}[0-9]{1,3}$")
		json.Node("$.ipv6").Matches("^[a-fA-F0-9:]+$")
		json.Node("$.byte").Matches("^[a-zA-Z0-9+\\/]+={0,2}$")
		json.Node("$.html").Contains("<html lang=\"en\">")
		json.Node("$.html").Matches("<[^>]+>|&[^;]+;")
		json.Node("$.shortString").IsStringWithLengthInRange(2, 4)
		json.Node("$.longString").IsStringWithLengthInRange(100, 105)
		json.Node("$.array").IsArrayWithElementsCount(5)
		json.Node("$.uniqueArrayOfObjects").IsArrayWithElementsCount(1)
		json.Node("$.uniqueArrayOfObjects[0].key").EqualToTheString("value")
		json.Node("$.freeForm").IsObjectWithPropertiesCount(1)
		json.Node("$.freeFormWithBooleanOption").IsObjectWithPropertiesCount(1)
		json.Node("$.hashMap").IsObjectWithPropertiesCount(1)
		json.Node("$.fixedHashMap").IsObjectWithPropertiesCount(1)
		json.Node("$.fixedHashMap.default").EqualToTheString("value")
		json.Node("$.writeOnlyBoolean").DoesNotExist()
		json.Node("$.writeOnlyInteger").DoesNotExist()
		json.Node("$.writeOnlyNumber").DoesNotExist()
		json.Node("$.writeOnlyString").DoesNotExist()
		json.Node("$.writeOnlyArray").DoesNotExist()
		json.Node("$.writeOnlyObject").DoesNotExist()
	})
}
