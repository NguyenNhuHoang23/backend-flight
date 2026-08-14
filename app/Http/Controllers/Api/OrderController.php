<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\PublicStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        try {
            // Eager loading 2 bảng quan hệ để tránh lỗi N+1 Query
            $orders = Order::with(['passengers', 'flights'])
                ->latest()
                ->paginate($request->get('per_page', 3));

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đơn hàng thành công',
                'data' => $orders,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách đơn hàng',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update order request', [
            'id' => $id,
            'request' => $request->all(),
        ]);
        try {
            $order = Order::where('order_code', $id)
                ->orWhere('id', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng',
                ], 404);
            }

            // Validate
            $request->validate([
                'transfer_content' => 'nullable|string|max:500',
                'payment_bill_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            ]);

            // =========================
            // TRANSFER CONTENT
            // =========================
            if ($request->has('transfer_content')) {
                $order->transfer_content = $request->input('transfer_content');
            }

            // =========================
            // PAYMENT BILL IMAGE
            // =========================
            if ($request->hasFile('payment_bill_image')) {
                PublicStorage::delete($order->getRawOriginal('payment_bill_image'));

                $path = PublicStorage::store(
                    $request->file('payment_bill_image'),
                    'payment-bills'
                );

                $order->payment_bill_image = $path;
            }

            $order->save();

            // Refresh để lấy dữ liệu mới nhất từ DB
            $order->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật đơn hàng thành công',
                'data' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'transfer_content' => $order->transfer_content,
                    'payment_bill_image' => $order->payment_bill_image,
                    'updated_at' => $order->updated_at,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {


            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật đơn hàng',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            // Tìm theo order_code hoặc id
            $order = Order::with([
                'passengers',
                'flights',
            ])
                ->where('order_code', $id)
                ->orWhere('id', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết đơn hàng thành công',
                'data' => $order,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get order detail error', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy chi tiết đơn hàng',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            /*
            |--------------------------------------------------------------------------
            | ORDER
            |--------------------------------------------------------------------------
            */
            'booking_at' => [
                'required',
                'date',
            ],

            'contact_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_phone' => [
                'required',
                'string',
                'max:30',
            ],

            'contact_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'total_amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'payment_method' => [
                'nullable',
                'string',
                'max:50',
            ],

            'transfer_content' => [
                'nullable',
                'string',
                'max:500',
            ],

            'payment_bill_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | PASSENGERS
            |--------------------------------------------------------------------------
            */
            'passengers' => [
                'required',
                'array',
                'min:1',
            ],

            'passengers.*.full_name' => [
                'required',
                'string',
                'max:255',
            ],

            'passengers.*.passenger_type' => [
                'nullable',
                'string',
                'max:30',
            ],

            'passengers.*.document_type' => [
                'nullable',
                'string',
                'max:30',
            ],

            'passengers.*.document_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'passengers.*.date_of_birth' => [
                'nullable',
                'date',
            ],

            /*
            |--------------------------------------------------------------------------
            | FLIGHTS
            |--------------------------------------------------------------------------
            */
            'flights' => [
                'required',
                'array',
                'min:1',
            ],

            'flights.*.trip_type' => [
                'required',
                'string',
                'in:outbound,return',
            ],

            'flights.*.airline_name' => [
                'required',
                'string',
                'max:255',
            ],

            'flights.*.airline_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'flights.*.flight_number' => [
                'required',
                'string',
                'max:30',
            ],

            'flights.*.departure_airport' => [
                'required',
                'string',
                'max:10',
            ],

            'flights.*.arrival_airport' => [
                'required',
                'string',
                'max:10',
            ],

            'flights.*.departure_at' => [
                'required',
                'date',
            ],

            'flights.*.arrival_at' => [
                'nullable',
                'date',
            ],
        ]);

        try {

            $order = DB::transaction(function () use ($request, $validated) {

                /*
                |--------------------------------------------------------------------------
                | Tạo mã đơn hàng
                |--------------------------------------------------------------------------
                */

                do {
                    $orderCode = 'ORD-' . now()->format('Y') . '-' . strtoupper(
                        Str::random(6)
                    );
                } while (Order::where('order_code', $orderCode)->exists());


                /*
                |--------------------------------------------------------------------------
                | Upload bill nếu có
                |--------------------------------------------------------------------------
                */

                $billPath = null;

                if ($request->hasFile('payment_bill_image')) {
                    $billPath = PublicStorage::store(
                        $request->file('payment_bill_image'),
                        'payment-bills'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Tạo ORDER
                |--------------------------------------------------------------------------
                */

                $order = Order::create([
                    'order_code' => $orderCode,

                    'status' => 'pending',

                    'booking_at' => $validated['booking_at'],

                    'contact_name' => $validated['contact_name'],

                    'contact_phone' => $validated['contact_phone'],

                    'contact_email' => $validated['contact_email'] ?? null,

                    'total_amount' => $validated['total_amount'],

                    'payment_method' => $validated['payment_method'] ?? null,

                    'payment_bill_image' => $billPath,

                    'transfer_content' => $validated['transfer_content'] ?? null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Tạo hành khách
                |--------------------------------------------------------------------------
                */

                foreach ($validated['passengers'] as $passenger) {

                    $order->passengers()->create([
                        'full_name' => $passenger['full_name'],

                        'passenger_type' =>
                        $passenger['passenger_type'] ?? 'adult',

                        'date_of_birth' =>
                        $passenger['date_of_birth'] ?? null,

                        'document_type' =>
                        $passenger['document_type'] ?? null,

                        'document_number' =>
                        $passenger['document_number'] ?? null,

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Tạo chặng bay
                |--------------------------------------------------------------------------
                */

                foreach ($validated['flights'] as $flight) {

                    $order->flights()->create([
                        'trip_type' => $flight['trip_type'],

                        'airline_name' => $flight['airline_name'],

                        'airline_code' =>
                        $flight['airline_code'] ?? null,

                        'flight_number' => $flight['flight_number'],

                        'departure_airport' =>
                        $flight['departure_airport'],

                        'arrival_airport' =>
                        $flight['arrival_airport'],

                        'departure_at' =>
                        $flight['departure_at'],

                        'arrival_at' =>
                        $flight['arrival_at'] ?? null,
                    ]);
                }


                return $order;
            });


            /*
            |--------------------------------------------------------------------------
            | Load relation
            |--------------------------------------------------------------------------
            */

            $order->load([
                'passengers',
                'flights',
            ]);


            return response()->json([
                'success' => true,

                'message' => 'Tạo đơn hàng thành công',

                'data' => $order,
            ], 201);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,

                'message' => 'Không thể tạo đơn hàng',

                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $order = Order::where('order_code', $id)
                ->orWhere('id', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng',
                ], 404);
            }

            DB::transaction(function () use ($order) {
                PublicStorage::delete($order->getRawOriginal('payment_bill_image'));
                $order->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Xóa đơn hàng thành công',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Delete order error', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa đơn hàng',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }
}
