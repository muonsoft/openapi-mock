package container

import (
	"github.com/sirupsen/logrus"
	"swagger-mock/internal/application/openapi/loader"
)

type Container interface {
	GetLogger() logrus.FieldLogger
	CreateLoader() loader.SpecificationLoader
}
