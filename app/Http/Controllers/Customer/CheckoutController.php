<?php

namespace App\Http\Controllers\Customer;

use App\Order;
use App\Billingaddress;
use App\Shipping_address;
use Auth;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\SslCommerz\SslCommerzNotification;
class CheckoutController extends Controller
{
    // for my_order
    public function my_order()
    {
        // for header
        $title = 'Order list';
        $orders = Order::where('user_id',Auth::id())->latest()->paginate(10);

        return view('customer.order',compact('title','orders'));
    }

    // for order_cancel
    public function order_cancel($order_id)
    {
        $order = Order::find($order_id);
        $order->order_status = 'Canceled';
        $order->save();

        Toastr::success('Order Successfully Cancled !','Success');
        return redirect()->back();
    }

    // for order_cancel
    public function order_success($order_id)
    {
        $order = Order::find($order_id);
        $order->status = "Paid";
        $order->order_status = 'Successed';
        $order->save();

        Toastr::success('Order Successfully Done !','Success');
        return redirect()->back();
    }

    // for order_view
    public function order_view($order_id)
    {
        $order = Order::find($order_id);
        if ($order->user_id == Auth::id()) {
           // for header
           $title = 'Order details';

           $shipping_address =Shipping_address::where('user_id',Auth::id())
                                                ->where('order_code',$order->order_code)->first();
           $billingaddress =Billingaddress::where('user_id',Auth::id())
                                            ->where('order_code',$order->order_code)->first();

           return view('customer.order_view',compact('title'
               ,'shipping_address','billingaddress','order'
           ));
        } else {
            return redirect()->back();
        }
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // for header
        $title = 'Checkout';
        return view('customer.checkout',compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'billing_name' => 'required|max:100',
            'billing_phone' => 'required|max:50',
            'billing_email' => 'required|max:100',
            'billing_address' => 'required|max:2000',

            'shipping_name' => 'required|max:100',
            'shipping_phone' => 'required|max:50',
            'shipping_email' => 'required|max:100',
            'shipping_address' => 'required|max:2000',

            'product_id' => 'required',
            'quantity' => 'required',
            'subtotal' => 'required',
            'payment_method' => 'required',
            'shipping_charge' => 'required|numeric',
        ]);        

        if ($request->product_id) {
          $product_id =trim(implode(",",$request->product_id),",");
        } else {
         $product_id = null;
        }

        if ($request->size_id) {
          $size_id =trim(implode(",",$request->size_id),",");
        } else {
         $size_id = null;
        }

        if ($request->colour_id) {
          $colour_id =trim(implode(",",$request->colour_id),",");
        } else {
         $colour_id = null;
        }

        if ($request->quantity) {
          $quantity =trim(implode(",",$request->quantity),",");
        } else {
         $quantity = null;
        }

        $last_ac = Order::select('id')->latest()->first();

        if (isset($last_ac)) {
            $order_code = 'O-'.sprintf('%04d',$last_ac->id+1);
        }else {
            $order_code = 'O-'.sprintf('%04d',1);
        }

        if ($request->waraper) {
            $waraper = $request->waraper;
            $total = $request->subtotal + $request->shipping_charge +$waraper;
        }else {
            $waraper = 0;
            $total = $request->subtotal + $request->shipping_charge +$waraper;
        }

        if ($request->payment_method=="Cash") {
            $order = new Order();
            $order->user_id = Auth::id();
            $order->order_code = $order_code;
            $order->product_id = $product_id;
            $order->size_id = $size_id;
            $order->colour_id = $colour_id;
            $order->quantity = $quantity;
            $order->subtotal = $request->subtotal;
            $order->shipping_charge = $request->shipping_charge;
            $order->waraper = $waraper;
            $order->total = $total;
            $order->payment_method = $request->payment_method;
            $order->transaction_id = uniqid();
            $order->save();

            // for billing address
            $billingaddress = new Billingaddress();
            $billingaddress->user_id = Auth::id();
            $billingaddress->order_code = $order_code;
            $billingaddress->name = $request->billing_name;
            $billingaddress->phone = $request->billing_phone;
            $billingaddress->email = $request->billing_email;
            $billingaddress->address = $request->billing_address;
            $billingaddress->save();

            // for Shipping address
            $shipping_address = new Shipping_address();
            $shipping_address->user_id = Auth::id();
            $shipping_address->order_code = $order_code;
            $shipping_address->name = $request->shipping_name;
            $shipping_address->phone = $request->shipping_phone;
            $shipping_address->email = $request->shipping_email;
            $shipping_address->address = $request->shipping_address;
            $shipping_address->save();

            session()->forget('cart');

            Toastr::success('Order done successfully.' ,'Success');
            return redirect()->route('customer.my_order');
        } else {
            // for sslcommerz
            $post_data = array();
            $post_data['total_amount'] = $total; # You cant not pay less than 10
            $post_data['currency'] = "BDT";
            $post_data['tran_id'] = uniqid(); // tran_id must be unique

            # CUSTOMER INFORMATION
            $post_data['cus_name'] = $request->billing_name;
            $post_data['cus_email'] = $request->billing_email;
            $post_data['cus_add1'] = $request->billing_address;
            $post_data['cus_add2'] = "";
            $post_data['cus_city'] = "";
            $post_data['cus_state'] = "";
            $post_data['cus_postcode'] = "";
            $post_data['cus_country'] = "Bangladesh";
            $post_data['cus_phone'] = $request->billing_phone;
            $post_data['cus_fax'] = "";

            # SHIPMENT INFORMATION
            $post_data['ship_name'] = $request->shipping_name;
            $post_data['ship_add1'] = $request->shipping_address;
            $post_data['ship_add2'] = "";
            $post_data['ship_city'] = "";
            $post_data['ship_state'] = "";
            $post_data['ship_postcode'] = "";
            $post_data['ship_phone'] = $request->shipping_phone;
            $post_data['ship_country'] = "Bangladesh";

            $post_data['shipping_method'] = "NO";
            $post_data['product_name'] = $order_code;
            $post_data['product_category'] = "Books";
            $post_data['product_profile'] = "physical-goods";

            # OPTIONAL PARAMETERS
            $post_data['value_a'] = "ref001";
            $post_data['value_b'] = "ref002";
            $post_data['value_c'] = "ref003";
            $post_data['value_d'] = "ref004";
            // sslcommerz end

            $order = new Order();
            $order->user_id = Auth::id();
            $order->order_code = $order_code;
            $order->product_id = $product_id;
            $order->size_id = $size_id;
            $order->colour_id = $colour_id;
            $order->quantity = $quantity;
            $order->subtotal = $request->subtotal;
            $order->shipping_charge = $request->shipping_charge;
            $order->waraper = $waraper;
            $order->total = $total;
            $order->payment_method = $request->payment_method;
            $order->transaction_id = $post_data['tran_id'];
            $order->save();

            // for billing address
            $billingaddress = new Billingaddress();
            $billingaddress->user_id = Auth::id();
            $billingaddress->order_code = $order_code;
            $billingaddress->name = $request->billing_name;
            $billingaddress->phone = $request->billing_phone;
            $billingaddress->email = $request->billing_email;
            $billingaddress->address = $request->billing_address;
            $billingaddress->save();

            // for Shipping address
            $shipping_address = new Shipping_address();
            $shipping_address->user_id = Auth::id();
            $shipping_address->order_code = $order_code;
            $shipping_address->name = $request->shipping_name;
            $shipping_address->phone = $request->shipping_phone;
            $shipping_address->email = $request->shipping_email;
            $shipping_address->address = $request->shipping_address;
            $shipping_address->save();

            $sslc = new SslCommerzNotification();
            # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
            $payment_options = $sslc->makePayment($post_data, 'hosted');

            if (!is_array($payment_options)) {
                print_r($payment_options);
                $payment_options = array();
            }
        }
                
    }

}
