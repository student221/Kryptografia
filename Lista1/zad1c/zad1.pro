TEMPLATE = app
CONFIG += console
CONFIG -= app_bundle
CONFIG -= qt
QMAKE_CFLAGS += -std=c99

LIBS += -lcrypto -lpthread

SOURCES += main.c
