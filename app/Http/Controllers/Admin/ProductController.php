<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SmsService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected SmsService $sms) {}

    /**
     * ویدیوهای محصولاتی که غرفه‌دار آپلود کرده و منتظر تأیید ادمین هستن،
     * قبل از تأیید هیچ‌جای سایت به مشتری نشون داده نمی‌شن (Product::video_url
     * این رو خودش رعایت می‌کنه)، این صفحه تنها راه دیدن و تأیید/ردشونه.
     */
    public function videos()
    {
        $products = Product::with('booth')
            ->where('video_status', 'pending')
            ->latest('updated_at')
            ->paginate(20);

        return view('admin.products.videos', compact('products'));
    }

    public function video(Product $product, string $status)
    {
        abort_unless(in_array($status, ['approved', 'rejected']), 404);

        $product->update(['video_status' => $status]);

        if ($product->booth?->owner?->phone) {
            $message = $status === 'approved'
                ? 'ویدیوی محصول «'.$product->localized_name.'» تأیید و روی سایت نمایش داده شد.'
                : 'ویدیوی محصول «'.$product->localized_name.'» توسط پشتیبانی تأیید نشد.';
            $this->sms->sendRaw($product->booth->owner->phone, $message);
        }

        return back()->with('success', 'وضعیت ویدیو تغییر کرد.');
    }
}
