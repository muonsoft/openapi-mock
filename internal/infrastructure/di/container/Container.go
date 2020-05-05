package container

import "github.com/sirupsen/logrus"

type Container interface {
	GetLogger() logrus.FieldLogger
}
