#include <iostream>
#include <termios.h>
#include <unistd.h>
#ifdef WIN32
#include <windows.h>
#endif
#include <cryptopp/osrng.h>

#include "filecrypto.h"

using namespace std;
using namespace CryptoPP;

int printError()
{
    cout << "Usage: tryb source dest schematszyfrowania trybszyfrowania sciezkadokeystore iv" << endl;
    return 0;
}

void SetStdinEcho(bool enable = true)
{
#ifdef WIN32
    HANDLE hStdin = GetStdHandle(STD_INPUT_HANDLE);
    DWORD mode;
    GetConsoleMode(hStdin, &mode);
    if (!enable)
        mode &= ~ENABLE_ECHO_INPUT;
    else mode |= ENABLE_ECHO_INPUT;
#else
    struct termios tty;
    tcgetattr(STDIN_FILENO, &tty);
    if (!enable)
        tty.c_lflag &= ~ECHO;
    else tty.c_lflag |= ECHO;
    (void)tcsetattr(STDIN_FILENO, TCSANOW, &tty);
#endif
}

///
///Supported cipher modes: AES, IDEA, Blowfish, Serpent, Camellia
///Supported operation modes: CBC, CTR, EAX, GCM
///

int main(int argc, char *argv[])
{
    if (argc != 8)
    {
        return printError();
    }

    int mode = atoi(argv[1]);
    if (mode < 0 || mode > 1)
        return printError();

    string sourceFile = argv[2];
    string destFile = argv[3];
    string cipherMode = argv[4];
    if (cipherMode != "AES")
        return printError();

    string operationMode = argv[5];
    if (operationMode != "CBC" && operationMode != "CTR" && operationMode != "EAX" && operationMode != "GCM")
        return printError();
    string keyStoreFile = argv[6];
    string iv_string = argv[7];

    byte iv[AES::BLOCKSIZE] = {};
    HexDecoder decoder;
    decoder.Put((byte*)iv_string.data(), iv_string.size());
    decoder.MessageEnd();
    decoder.Get(iv, sizeof(iv));

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
        FileCrypto::EncryptFile(sourceFile, destFile, cipherMode, operationMode, keyStoreFile, iv, key, password);
    }
    else if (mode == 1)
        FileCrypto::DecryptFile(sourceFile, destFile, cipherMode, operationMode, keyStoreFile, iv, password);

    return 0;
}
