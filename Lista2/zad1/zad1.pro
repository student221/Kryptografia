TEMPLATE = app
CONFIG += console c++11
CONFIG -= app_bundle
CONFIG -= qt

LIBS += -L"/usr/local/lib" -lcryptopp
LIBS += -lpthread

SOURCES += main.cpp \
    filecrypto.cpp

HEADERS += \
    filecrypto.h
