<?php

namespace Modules\Chat\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Chat\Models\Message;
use Modules\Chat\Models\SpecialMessage;

class SpecialMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'name' => 'ارسال اطلاعات تماس',
                'content' => '',
                'controller_method' => 'sendInfo',
                'status' => '[1,2]'
            ],
            [
                'id' => 2,
                'name' => 'نیاز به مراجعه تلفنی',
                'content' => 'سوال شما از طریق مشاوره متنی قابل تشخیص نیست و نیاز به ویزیت ویدئویی دارد.بنابراین زوپ مبلغ این مشاوره را به اعتبار شما در زوپ واریز نموده.همچنین این مشاوره به طور خودکار توسط تیم پشتیبانی زوپ بسته می شود. شما می توانید جهت ویزیت ویدئویی از دکمه زیر استفاده کنید تا مشاوره بهتر و دقیق تری به شما ارائه دهم.',
                'controller_method' => 'phoneCallNeeded',
                'status' => '[1,2]'
            ],
            [
                'id' => 3,
                'name' => 'بلاک کردن کاربر',
                'content' => 'شما از سمت پزشک بلاک شدید و دیگر امکان مشاوره با این پزشک را از طریق زوپ نخواهید داشت. ',
                'controller_method' => 'blockUser',
                'status' => '[1,2]'
            ],
            [
                'id' => 4,
                'name' => 'اتمام مشاوره و انتقال به آرشیو',
                'content' => 'مشاوره خاتمه یافت و به آرشیو منتقل شد. ',
                'controller_method' => 'consultingEndedAndArchived',
                'status' => '[1,2]'
            ],
            [
                'id' => 5,
                'name' => 'انتقال به آرشیو',
                'content' => 'مشاوره به آرشیو منتقل شد. ',
                'controller_method' => 'consultingArchived',
                'status' => '[1,2]'
            ],
            [
                'id' => 6,
                'name' => 'اتمام مشاوره',
                'content' => 'مشاوره خاتمه یافت. ',
                'controller_method' => 'consultingEnded',
                'status' => '[1,2]'
            ],
            [
                'id' => 7,
                'name' => 'رتبه دهی به پزشک',
                'content' => '',
                'controller_method' => 'rateToDoctor',
                'status' => '[3]'
            ],
            [
                'id' => 8,
                'name' => 'تشکر از پزشک',
                'content' => '🙏',
                'controller_method' => '',
                'status' => '[3]'
            ],
            [
                'id' => 9,
                'name' => 'درخواست استرداد وجه',
                'content' => 'درخواست استرداد وجه با موفقیت ارسال شد. ',
                'controller_method' => 'refundRequest',
                'status' => '[3]'
            ],
            [
                'id' => 10,
                'name' => 'مطرح کردن در بخش مشاوره پزشکی ',
                'content' => 'کاربر گرامی با سلام ، برای پرسیدن سوال و مشاوره پزشکی در زوپ ثبت نام کنید و از پزشک با تخصص مربوطه سوال خود را بپرسید. ',
                'controller_method' => '',
                'status' => '[4]'
            ],
            [
                'id' => 11,
                'name' => 'در علایق من نیست ',
                'content' => 'پزشک مورد نظر امکان پاسخگویی به شما را ندارد . لطفا برای سوال خود پزشک دیگری انتخاب کنید و یا با پشتیبانی تماس بگیرید. ',
                'controller_method' => 'notInMyInterests',
                'status' => '[1,2]'
            ],
            [
                'id' => 12,
                'name' => 'در تخصص من نیست',
                'content' => 'پزشک مورد نظر امکان پاسخگویی به شما را ندارد . لطفا برای سوال خود پزشک دیگری انتخاب کنید و یا با پشتیبانی تماس بگیرید. ',
                'controller_method' => 'notMySpecialty',
                'status' => '[1,2]'
            ],

        ];
        SpecialMessage::insert($data);
    }
}
