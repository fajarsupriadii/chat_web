<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Client;

class ChatbotController extends Controller
{
    public function checkCid(Request $request)
    {
        $cid = $request->query('cid');

        if (!$cid) {
            return response()->json([
                'message' => 'CID No cannot be blank',
            ], 500);
        }

        return response()->json([
            'cid' => $cid,
            'name' => 'Mockup',
            'address' => 'Batununggal',
            'package_name' => 'Silver',
            'package_price' => 280000
        ]);
    }

    public function currentBilling(Request $request)
    {
        $cid = $request->query('cid');

        if (!$cid) {
            return response()->json([
                'message' => 'CID No cannot be blank',
            ], 500);
        }

        return response()->json([
            'billing_amount' => 280000,
            'billing_period' => 'Oktober',
        ]);
    }

    public function billingHistory(Request $request)
    {
        $cid = $request->query('cid');

        if (!$cid) {
            return response()->json([
                'message' => 'CID No cannot be blank',
            ], 500);
        }

        return response()->json([
            [
                'billing_amount' => 280000,
                'billing_period' => 'Oktober',
                'paid_status' => 'Not paid'
            ],
            [
                'billing_amount' => 280000,
                'billing_period' => 'September',
                'paid_status' => 'Paid'
            ],
            [
                'billing_amount' => 280000,
                'billing_period' => 'Agustus',
                'paid_status' => 'Paid'
            ]
        ]);
    }

    public function technicalStatus(Request $request)
    {
        $cid = $request->query('cid');
        $type = $request->query('type');
        $status = 'no_issue';
        $statusDetail = 'No issue';

        if (!$cid) {
            return response()->json([
                'message' => 'CID No cannot be blank',
            ], 500);
        }

        switch ($type) {
            case 'block':
                $status = 'block';
                $statusDetail = 'Block';
                break;
            case 'outage':
                $status = 'outage';
                $statusDetail = 'Outage';
                break;
            default:
                break;
        }

        return response()->json([
            'status' => $status,
            'status_detail' => $statusDetail
        ]);
    }

    public function createSo(Request $request)
    {
        return response()->json([
            'cid' => $request->input('cid'),
            'category' => $request->input('category'),
            'subcategory' => $request->input('subcategory'),
            'phone_no' => $request->input('phone_no'),
            'so_no' => 111999
        ]);
    }

    public function packageCategory(Request $request)
    {
        $cid = $request->query('cid');
        $results = [
            [
                'category' => 'Category 1',
                'category_detail' => 'Detail category 1',
            ],
            [
                'category' => 'Category 2',
                'category_detail' => 'Detail category 2',
            ],
            [
                'category' => 'Category 3',
                'category_detail' => 'Detail category 3',
            ]
        ];

        if ($cid) {
            array_shift($results);
        }

        return response()->json($results);
    }

    public function packageSpeed(Request $request)
    {
        $cid = $request->query('cid');

        if (!$cid) {
            return response()->json([
                'message' => 'CID No cannot be blank',
            ], 500);
        }

        return response()->json([
            [
                'package_name' => '20 Mbps',
                'package_detail' => 'Upgrage speed 20 Mbps',
                'package_price' => 250000
            ],
            [
                'package_name' => '50 Mbps',
                'package_detail' => 'Upgrage speed 50 Mbps',
                'package_price' => 450000
            ],
        ]);
    }

    public function upgradeRequest(Request $request)
    {
        return response()->json([
            'cid' => $request->input('cid'),
            'package' => $request->input('package'),
            'so_no' => 222999
        ]);
    }

    public function package(Request $request)
    {
        $category = $request->query('package_category');

        if (!$category) {
            return response()->json([
                'message' => 'Package Category No cannot be blank',
            ], 500);
        }

        return response()->json([
            [
                'package' => 'Package 1',
                'package_detail' => 'Detail package 1',
            ],
            [
                'package' => 'Package 2',
                'package_detail' => 'Detail package 2',
            ],
            [
                'package' => 'Package 3',
                'package_detail' => 'Detail package 3',
            ]
        ]);
    }

    public function newConnect(Request $request)
    {
        return response()->json([
            'name' => $request->input('name'),
            'phone_no' => $request->input('phone_no'),
            'email' => $request->input('email'),
            'package_category' => $request->input('package_category'),
            'package' => $request->input('package'),
            'so_no' => 333999
        ]);
    }
}
