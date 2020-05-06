package apitest

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/stretchr/testify/suite"
	"net/http"
	"swagger-mock/internal/di/config"
	"swagger-mock/internal/di/container"
	"testing"
)

type APISuite struct {
	suite.Suite
}

func TestApi(t *testing.T) {
	suite.Run(t, new(APISuite))
}

func (suite *APISuite) createOpenAPIHandler(configuration config.Configuration) http.Handler {
	router := openapi3filter.NewRouter().WithSwaggerFromFile("./../../resources/openapi-files/" + configuration.SpecificationURL)
	diContainer := container.New(configuration)

	return diContainer.CreateOpenAPIHandler(router)
}
