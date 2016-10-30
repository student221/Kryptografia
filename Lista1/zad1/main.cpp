#include <cryptopp/cryptlib.h>
#include <cryptopp/hex.h>
#include <cryptopp/aes.h>
#include <cryptopp/ccm.h>
#include <cryptopp/base64.h>
#include <iostream>
#include <string>
#include <fstream>
#include <iomanip>
#include <thread>
#include <ctime>

using std::cout;
using std::endl;
using std::string;
using std::thread;
using std::ofstream;

void calculate(const string & from, const string & to, const string & sufix, byte iv[], const string  &cipher);

int main(int argc, char * argv[])
{
    string sufix = "9bce892497299e7c8b9ecc8200001ffd07620c430a0d7a42195b4eca";
    string iv_string = "c71c1b129cbcd5ea5cda81a43b577386";
    string cipher_string = "iwrh/zpKMrSejmCTurc0qH9SfH3q18tyYQNsor9UAFt8ZBQL+Ea+5uhzwBncUz08dbJ8d/IpWwnTpdvisp3gO5OTBvxQmSzI4e6G3uNGX7llLiH9kMtXUmKvmIIYOANs";

    byte iv[CryptoPP::AES::BLOCKSIZE] = {};
    CryptoPP::HexDecoder decoder;
    decoder.Put((byte*)iv_string.data(), iv_string.size());
    decoder.MessageEnd();
    decoder.Get(iv, sizeof(iv));

    string cipher = "";
    CryptoPP::StringSource ss(cipher_string, true,
        new CryptoPP::Base64Decoder(
            new CryptoPP::StringSink(cipher)
        ) // HexEncoder
    ); // StringSource

    thread threads[4];

    threads[0] = thread(calculate, "00000000", "00400000", sufix, iv, cipher);
    threads[1] = thread(calculate, "00400001", "00800000", sufix, iv, cipher);
    threads[2] = thread(calculate, "00800001", "00c00000", sufix, iv, cipher);
    threads[3] = thread(calculate, "00c00001", "00ffffff", sufix, iv, cipher);

  //  std::chrono::steady_clock::time_point begin = std::chrono::steady_clock::now();

    for (int i = 0; i < 4; ++i)
    {
        threads[i].join();
    }

  /*  std::chrono::steady_clock::time_point end= std::chrono::steady_clock::now();

    float seconds = std::chrono::duration_cast<std::chrono::seconds>(end - begin).count();
    unsigned long maxKeys = 4294967295;
    long keys = 16777215;
    std::cout << "Time for " << keys << " keys: " << seconds << " sec\n";
    double estimatedSeconds = seconds * (double)maxKeys / (double)keys;
    std::cout << "Estimated time for " << maxKeys << " " << estimatedSeconds << " " << std::endl;*/

    return 0;
}

void calculate(const string & from, const string & to, const string & sufix, byte iv[], const string  &cipher)
{
    ofstream file("from" + from + "to" + to + ".txt");

    int length = from.length() - 1;
    CryptoPP::CBC_Mode<CryptoPP::AES>::Decryption decryption;

    string key_string = from + sufix;
    int cvt[UCHAR_MAX+1] = {0};
    cvt['0'] = 0;
    cvt['1'] = 1;
    cvt['2'] = 2;
    cvt['3'] = 3;
    cvt['4'] = 4;
    cvt['5'] = 5;
    cvt['6'] = 6;
    cvt['7'] = 7;
    cvt['8'] = 8;
    cvt['9'] = 9;
    cvt['a'] = 10;
    cvt['b'] = 11;
    cvt['c'] = 12;
    cvt['d'] = 13;
    cvt['e'] = 14;
    cvt['f'] = 15;

    byte key[32];
    for (int i = 0; i < 32; i++) {
        key[i] = 16 * cvt[(unsigned char)key_string[2*i]] +
                      cvt[(unsigned char)key_string[2*i + 1]];
    }

    for (string prefix = from; prefix != to; )
    {
        std::string decrypted_text;

        decryption.SetKeyWithIV(key, 32, iv);

        CryptoPP::StringSource encryptor(cipher, true,
            new CryptoPP::StreamTransformationFilter(decryption,
                    new CryptoPP::StringSink(decrypted_text),
                       CryptoPP::StreamTransformationFilter::NO_PADDING
            )
        );
        encryptor.PumpAll();

        bool found = true;

        int c,ix,n,j;
        for (int i = 0, ix= decrypted_text.length(); i < ix; i++)
        {
            c = (unsigned char) decrypted_text[i];
            if (0x00 <= c && c <= 0x7f) n=0;
            else if ((c & 0xE0) == 0xC0) n=1;
            else if ( c==0xed && i<(ix-1) && ((unsigned char)decrypted_text[i+1] & 0xa0)==0xa0)
            {
                found = false;
                break;
            }
            else if ((c & 0xF0) == 0xE0) n=2;
            else if ((c & 0xF8) == 0xF0) n=3;
            else
            {
                found = false;
                break;
            }
            for (j=0; j<n && i<ix; j++) {
                if ((++i == ix) || (( (unsigned char)decrypted_text[i] & 0xC0) != 0x80))
                {
                    found = false;
                    break;
                }
            }
        }

       // if (found)
       //     file << decrypted_text  << " key: " << key_string << std::endl;
        cout << decrypted_text << endl;

        int iter = length;
        while (iter >= 0)
        {
            if (++prefix[iter] == ':')
                prefix[iter] = 'a';
            else if (prefix[iter] == 'g')
            {
                prefix[iter] = '0';
                --iter;
                continue;
            }
            break;
        }

        key_string = prefix + sufix;

       for (int i = 0; i < length; i++) {
            key[i] = 16 * cvt[(unsigned char)key_string[2*i]] +
                          cvt[(unsigned char)key_string[2*i + 1]];
        }
    }

    file.close();
}
