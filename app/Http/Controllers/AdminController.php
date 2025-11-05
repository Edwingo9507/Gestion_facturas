<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Factura;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Muestra el panel de administración
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $facturas = Factura::all();
        return view('admin.index', compact('facturas'));
    }

    /**
     * Muestra el formulario de login
     */
    public function showLogin()
    {
        return view('admin.login');
    }

    /**
     * Procesa el login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.index'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    /**
     * Importa facturas desde CSV
     */
    public function importCsv(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->store('temp');

        try {
            $data = $this->parseCsv(Storage::path($path));
            $imported = 0;
            $errors = [];

            foreach ($data as $row) {
                try {
                    Factura::create([
                        'documento' => $row['documento'],
                        'nombre_cliente' => $row['nombre_cliente'],
                        'valor' => $row['valor'],
                        'fecha_vencimiento' => $row['fecha_vencimiento'],
                        'pagada' => in_array(strtolower($row['estado'] ?? 'pendiente'), ['pagada', '1', 'true']),
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Error en fila " . ($imported + count($errors) + 1) . ": " . $e->getMessage();
                }
            }

            Storage::delete($path);

            return back()->with('success', "Se importaron {$imported} facturas exitosamente." . (!empty($errors) ? " Errores: " . implode(', ', $errors) : ''));

        } catch (\Exception $e) {
            Storage::delete($path);
            return back()->with('error', 'Error al procesar el archivo CSV: ' . $e->getMessage());
        }
    }

    /**
     * Parsea el archivo CSV
     */
    private function parseCsv($filePath)
    {
        $data = [];
        $handle = fopen($filePath, 'r');

        // Leer encabezados
        $headers = fgetcsv($handle, 1000, ',');

        // Validar encabezados requeridos
        $requiredHeaders = ['documento', 'nombre_cliente', 'valor'];
        foreach ($requiredHeaders as $header) {
            if (!in_array($header, $headers)) {
                throw new \Exception("El archivo CSV debe contener la columna '{$header}'");
            }
        }

        // Leer datos
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($row) === count($headers)) {
                $rowData = array_combine($headers, $row);
                // Generar fecha de vencimiento automáticamente (30 días desde hoy)
                $rowData['fecha_vencimiento'] = now()->addDays(30)->toDateString();
                $data[] = $rowData;
            }
        }

        fclose($handle);
        return $data;
    }

    /**
     * Elimina una factura
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $factura = Factura::findOrFail($id);
        $factura->delete();

        return back()->with('success', 'Factura eliminada exitosamente.');
    }
}
