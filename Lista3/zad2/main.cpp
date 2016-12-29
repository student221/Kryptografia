#include <iostream>
#include <termios.h>
#include <unistd.h>

#include <cryptopp/osrng.h>
#include "filecrypto.h"

using namespace std;
using namespace CryptoPP;

int printError()
{
    cout << "Usage: tryb source dest keystorefile conffile" << endl;
    return 0;
}

void SetStdinEcho(bool enable = true)
{
    struct termios tty;
    tcgetattr(STDIN_FILENO, &tty);
    if (!enable)
        tty.c_lflag &= ~ECHO;
    else tty.c_lflag |= ECHO;
    (void)tcsetattr(STDIN_FILENO, TCSANOW, &tty);
}

int main(int argc, char *argv[])
{
    if (argc != 6)
    {
        return printError();
    }

    int mode = atoi(argv[1]);
    if (mode < 0 || mode > 1)
        return printError();

    string sourceFile = argv[2];
    string destFile = argv[3];
    string keyStoreFile = argv[4];
    string confFile = argv[5];

    SetStdinEcho(false);
    cout << "Please enter your password: ";
    string password;
    getline(cin, password);
    SetStdinEcho(true);

    if (mode == 0)
    {
        AutoSeededRandomPool rnd;
        SecByteBlock key(0x00, AES::DEFAULT_KEYLENGTH);
        rnd.GenerateBlock(key, key.size());

        SecByteBlock iv(0x00, AES::BLOCKSIZE);
        rnd.GenerateBlock(iv, iv.size());

        FileCrypto::EncryptFile(sourceFile, destFile, keyStoreFile, confFile, iv, key, password);
    }
    else if (mode == 1)
    {
        FileCrypto::DecryptFile(sourceFile, destFile, confFile, password);
    }

    return 0;
}
