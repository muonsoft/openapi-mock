package apitest

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/stretchr/testify/suite"
	"net/http"
	"swagger-mock/internal/application/config"
	"swagger-mock/internal/application/container"
	"testing"
)

type APISuite struct {
	suite.Suite
}

func TestApi(t *testing.T) {
	suite.Run(t, new(APISuite))
}

func (suite *APISuite) createOpenAPIHandler(configuration config.Configuration) http.Handler {
	specificationPath := "./../../resources/openapi-files/" + configuration.SpecificationURL
	diContainer := container.New(&configuration)
	specificationLoader := diContainer.CreateSpecificationLoader()
	specification, err := specificationLoader.LoadFromURI(specificationPath)
	if err != nil {
		panic(err)
	}
	router := openapi3filter.NewRouter().WithSwagger(specification)

	return diContainer.CreateHTTPHandler(router)
}
