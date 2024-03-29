package logcontext

import (
	"context"
	"io"
	"testing"

	"github.com/sirupsen/logrus"
	"github.com/stretchr/testify/assert"
)

func TestWithLogger_Logger_LoggerSetToContext(t *testing.T) {
	background := context.Background()
	logger := logrus.New()

	ctx := WithLogger(background, logger)

	assert.Equal(t, logger, ctx.Value(loggerKey))
}

func TestLoggerFromContext_EmptyContext_NullLoggerReturned(t *testing.T) {
	ctx := context.Background()

	logger := LoggerFromContext(ctx)

	assert.Equal(t, io.Discard, logger.(*logrus.Logger).Out)
}

func TestLoggerFromContext_ContextWithLogger_LoggerReturned(t *testing.T) {
	expectedLogger := logrus.New()
	ctx := context.WithValue(context.Background(), loggerKey, expectedLogger)

	logger := LoggerFromContext(ctx)

	assert.Equal(t, expectedLogger, logger)
}
