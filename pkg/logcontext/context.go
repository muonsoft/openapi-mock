package logcontext

import (
	"context"
	"github.com/sirupsen/logrus"
	"io/ioutil"
)

type key int

const loggerKey key = iota

func WithLogger(ctx context.Context, logger logrus.FieldLogger) context.Context {
	return context.WithValue(ctx, loggerKey, logger)
}

func LoggerFromContext(ctx context.Context) logrus.FieldLogger {
	logger, exists := ctx.Value(loggerKey).(logrus.FieldLogger)

	if !exists {
		nullLogger := logrus.New()
		nullLogger.Out = ioutil.Discard
		logger = nullLogger
	}

	return logger
}
