#include "filecrypto.h"

#include <iostream>
#include <vector>
#include <sstream>
#include <fstream>
#include <iterator>
#include <cryptopp/files.h>
#include <cryptopp/default.h>
#include <cryptopp/gcm.h>
#include <cryptopp/eax.h>
#include <cryptopp/aes.h>
#include <cryptopp/base64.h>
#include <irrKlang.h>
#include <mpg123.h>
#include <unistd.h>
#include "conio.h"

using namespace CryptoPP;
using namespace std;

#pragma comment(lib, "irrKlang.lib")

void FileCrypto::EncryptFile(const string &sourceFile, const string &destFile, const string &keyStoreFile, const string &confFile,
                             SecByteBlock &iv, SecByteBlock &key, const string &password)
{
    string key_string;
    HexEncoder hex(new StringSink(key_string));
    hex.Put(key, key.size());
    hex.MessageEnd();

    //save key to keystore
    StringSource ss(key_string, true,
           new DefaultEncryptorWithMAC(
               (byte*)password.data(), password.size(),
                  new FileSink(keyStoreFile.c_str())));

    string key2_string = "cf16eba09bce892497299e7c8b9ecc8200001ffd07620c430a0d7a42195b4eca";
    byte key2[AES::DEFAULT_KEYLENGTH];
    HexDecoder decoder;
    decoder.Put((byte*)key2_string.data(), key2_string.size());
    decoder.MessageEnd();
    decoder.Get(key2, sizeof(key2));

    string iv2_string = "52284b1d8396c11f0dbc926f9b5c9e1b";
    byte iv2[AES::BLOCKSIZE];
    HexDecoder decoderIv;
    decoderIv.Put((byte*)iv2_string.data(), iv2_string.size());
    decoderIv.MessageEnd();
    decoderIv.Get(iv2, sizeof(iv2));

    CBC_Mode<AES>::Encryption enc;
    enc.SetKeyWithIV(key2, sizeof(key2), iv2);

    string iv_string;
    HexEncoder ivEnc(new StringSink(iv_string));
    ivEnc.Put(iv, iv.size());
    ivEnc.MessageEnd();
    string confFileData = keyStoreFile + "\n" + iv_string + "\n" + password;

    //save keyStoreFile path, iv, password to conffile
    StringSource ss0(confFileData, true,
                    new StreamTransformationFilter(enc,
                        new FileSink(confFile.c_str())));

    CTR_Mode<AES>::Encryption encryption;
    encryption.SetKeyWithIV(key, key.size(), iv);

    //encrypt file
    FileSource fs(sourceFile.c_str(), true,
              new StreamTransformationFilter(encryption,
                    new FileSink(destFile.c_str())));

    cout << "\nFile encrypted successfully\n";
}

void FileCrypto::DecryptFile(const string &sourceFile, const string &destFile,
                             const string &confFile, const string &password)
{
    try
    {
        //open confFile
        string key2_string = "cf16eba09bce892497299e7c8b9ecc8200001ffd07620c430a0d7a42195b4eca";
        byte key2[AES::DEFAULT_KEYLENGTH];
        HexDecoder decoder;
        decoder.Put((byte*)key2_string.data(), key2_string.size());
        decoder.MessageEnd();
        decoder.Get(key2, sizeof(key2));

        string iv2_string = "52284b1d8396c11f0dbc926f9b5c9e1b";
        byte iv2[AES::BLOCKSIZE];
        HexDecoder decoderIv2;
        decoderIv2.Put((byte*)iv2_string.data(), iv2_string.size());
        decoderIv2.MessageEnd();
        decoderIv2.Get(iv2, sizeof(iv2));

        CBC_Mode<AES>::Decryption confFileDec;
        confFileDec.SetKeyWithIV(key2, sizeof(key2), iv2);

        string confFileContent;
        FileSource fs(confFile.c_str(), true,
                      new StreamTransformationFilter(confFileDec,
                            new StringSink(confFileContent)));

        //check password
        vector<string> content = split(confFileContent, '\n');
        if (content[2] != password)
            throw DefaultDecryptor::KeyBadErr();

        //load key
        string key_string;
        string keyFromFile;
        FileSource fsd(content[0].c_str(), true,
                       new DefaultDecryptorWithMAC(
                           (byte*)password.data(), password.size(),
                              new StringSink(keyFromFile)));

        HexDecoder hexDecoder(new StringSink(key_string));
        hexDecoder.Put((byte*)keyFromFile.c_str(), keyFromFile.size());
        hexDecoder.MessageEnd();

        SecByteBlock key((byte*)key_string.c_str(), 16);

        //load iv
        byte iv[AES::BLOCKSIZE];
        HexDecoder decoderIv;
        decoderIv.Put((byte*)content[1].data(), content[1].size());
        decoderIv.MessageEnd();
        decoderIv.Get(iv, sizeof(iv));

        CTR_Mode<AES>::Decryption decryption;
        decryption.SetKeyWithIV(key, key.size(), iv);

        string str;
        //decrypt file
        FileSource fsd2(sourceFile.c_str(), false,
              new StreamTransformationFilter(decryption,
                new StringSink(str)));

        irrklang::ISoundEngine * engine = irrklang::createIrrKlangDevice();

        while (!fsd2.SourceExhausted())
        {
            str = "";
            fsd2.Pump(131072);
            engine->addSoundSourceFromMemory((byte*)str.c_str(), str.size(), "sound.mp3");
            irrklang::ISound* snd = engine->play2D("sound.mp3", false, false, true);

            while (!snd->isFinished()) { }

            snd->drop();
            snd = 0;
            engine->removeAllSoundSources();
        }

        engine->drop();

        cout << "\nDecrpyted file successfully\n";
    }
    catch (DefaultDecryptor::KeyBadErr)
    {
        cerr << "\nInvalid password or keystore file\nTerminating\n";
        return;
    }
}

void FileCrypto::split(const string &s, char delim, vector<string> &elems) {
    stringstream ss;
    ss.str(s);
    string item;
    while (std::getline(ss, item, delim)) {
        elems.push_back(item);
    }
}

vector<string> FileCrypto::split(const string &s, char delim) {
    vector<string> elems;
    split(s, delim, elems);
    return elems;
}
