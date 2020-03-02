<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\PearsonCorrelationController;
use App\Http\Controllers\MahasiswaController;

class SiswaController extends Controller
{
    private $mata_pelajaran = array(
        "mtk" => 1,
        "ind" => 2,
        "ing" => 3,
        "fsk" => 4,
        "gbr" => 5,
        "pkn" => 6,
        "kma" => 7,
    );

    function index(Request $request)
    {
        // untuk penampung input dari form
        $data = $request->input();
        // untuk menampung input yang sudah diolah, agar mudah digunakan
        $siswa = $this->dataSiswa($data);

        // inisialisasi controller mahasiswa
        $mahasiswa = new MahasiswaController();
        // data mahasiswa
        $mhs = $mahasiswa->index($siswa["btn"])->toArray();

        // inisialisasi controller pearson correlation
        $pc = new PearsonCorrelationController();
        // melakukan perhitungan kemiripan
        $pearson = $pc->calculatePearson($mhs, $siswa);
        // menghitung prediksi
        $predict = $pc->calculatePredict($pearson);

        return view('/result', ['predict' => $predict, 'pearson' => $pearson]);
    }

    private function dataSiswa($data)
    {
        $i = 1;
        $result = array();
        $result["nilai"] = array();
        foreach ($data as $key => $value) {
            if ($key == "_token") {
                $result[$key] = $value;
            } else {
                if ($i == 1) {
                    // key untuk mata pelajaran
                    $k = substr($key, 0, 3);
                    // temporary array
                    $temp = [];
                    // masukan data (nilai) ke temp
                    array_push($temp, ((int) $value / 20) - 1);
                    $i++;
                } else {
                    // masukan data nilai ke temp
                    array_push($temp, ((int) $value / 20) - 1);
                    $i++;

                    if ($i == 5) {
                        // avg nilai

                        $avg = array_sum($temp) / count($temp);
                        array_push($temp, $avg);

                        // replace index ke-4 dengan AVG
                        $temp = $this->replaceKey($temp, 4, "AVG");

                        array_push($temp, $this->mata_pelajaran[$k]);

                        $temp = $this->replaceKey($temp, 5, "id_mata_pelajaran");

                        // masukin data ke result
                        // $result[$this->mata_pelajaran[$k]] = $temp;
                        array_push($result["nilai"], $temp);
                        // print($k." ");
                        $i = 1;
                    }
                }
            }
        }

        if (!empty($data["btnIPA"])) {
            $result["btn"] = "IPA";
        } else if (!empty($data["btnIPS"])) {
            $result["btn"] = "IPS";
        }
        return $result;
    }

    private function replaceKey($temp, $oldKey, $newKey)
    {
        $temp[$newKey] = $temp[$oldKey];
        unset($temp[$oldKey]);

        return $temp;
    }
}
