<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(config('app.name', 'АвтоСТО')); ?> — автосервис</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        body { font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    <header class="border-b border-slate-800/80 bg-slate-950/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 font-bold text-lg tracking-tight">
                <span class="w-9 h-9 rounded-lg bg-amber-500 flex items-center justify-center text-slate-950 text-sm">СТО</span>
                <span>Авто<span class="text-amber-400">Мастер</span></span>
            </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('login')): ?>
                <nav class="flex items-center gap-3 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('dashboard')); ?>" class="px-4 py-2 rounded-lg bg-amber-500 text-slate-950 font-semibold hover:bg-amber-400 transition">Личный кабинет</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="px-4 py-2 rounded-lg border border-slate-600 hover:border-amber-500 hover:text-amber-400 transition">Вход</a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('register')): ?>
                            <a href="<?php echo e(route('register')); ?>" class="px-4 py-2 rounded-lg bg-amber-500 text-slate-950 font-semibold hover:bg-amber-400 transition">Регистрация</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </nav>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </header>

    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-amber-950/40"></div>
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 50%, #f59e0b33 0%, transparent 50%), radial-gradient(circle at 80% 20%, #334155 0%, transparent 40%);"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 py-20 lg:py-28 grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <p class="text-amber-400 font-semibold text-sm uppercase tracking-widest mb-4">Профессиональный автосервис</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight text-white mb-6">
                    Ремонт и обслуживание<br>вашего автомобиля
                </h1>
                <p class="text-slate-400 text-lg mb-8 max-w-lg">
                    Диагностика, ТО, кузовные работы и оригинальные запчасти. Прозрачные цены, онлайн-запись и контроль статуса ремонта в личном кабинете.
                </p>
                <div class="flex flex-wrap gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('orders.index')); ?>" class="px-6 py-3 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 transition shadow-lg shadow-amber-500/25">Мои заказы</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('register')); ?>" class="px-6 py-3 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 transition shadow-lg shadow-amber-500/25">Записаться онлайн</a>
                        <a href="<?php echo e(route('login')); ?>" class="px-6 py-3 rounded-xl border border-slate-600 font-semibold hover:border-amber-500 transition">Войти в кабинет</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="hidden lg:block relative">
                <div class="aspect-square max-w-md mx-auto rounded-3xl bg-gradient-to-tr from-slate-800 to-slate-700 border border-slate-600 p-8 flex flex-col justify-end shadow-2xl">
                    <div class="absolute top-8 right-8 w-24 h-24 rounded-full bg-amber-500/20 blur-2xl"></div>
                    <svg class="w-full h-40 text-amber-500/90 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M8 17h8M6 11h12l-1-4H7l-1 4zM5 17a2 2 0 104 0m6 0a2 2 0 104 0"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 11l2-5h12l2 5"/>
                    </svg>
                    <ul class="space-y-3 text-sm text-slate-300">
                        <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-green-400"></span> 3 бокса — свободные слоты онлайн</li>
                        <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Склад запчастей на месте</li>
                        <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-400"></span> Оплата с баланса клиента</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="py-16 bg-slate-900/50 border-y border-slate-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <h2 class="text-3xl font-bold text-white mb-2">Наши услуги</h2>
            <p class="text-slate-400 mb-10">Актуальный прайс из системы учёта</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($services->isEmpty()): ?>
                <p class="text-slate-500">Прайс обновляется. Позвоните для уточнения стоимости.</p>
            <?php else: ?>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <article class="rounded-2xl bg-slate-800/80 border border-slate-700 p-6 hover:border-amber-500/50 transition group">
                            <h3 class="font-bold text-lg text-white group-hover:text-amber-400 transition"><?php echo e($service->name); ?></h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($service->description): ?>
                                <p class="text-slate-400 text-sm mt-2 line-clamp-3"><?php echo e($service->description); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <p class="mt-4 text-2xl font-extrabold text-amber-400"><?php echo e(number_format((float) $service->price, 0, '.', ' ')); ?> ₽</p>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    <section id="contacts" class="py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 grid md:grid-cols-2 gap-12">
            <div>
                <h2 class="text-3xl font-bold text-white mb-6">Контакты</h2>
                <ul class="space-y-4 text-slate-300">
                    <li>
                        <span class="text-slate-500 text-sm block">Адрес</span>
                        <strong class="text-white">г. Саратов, ул. Центральная, 6</strong>
                        <span class="text-slate-500 text-sm"> (цех «Основной», боксы 1–3)</span>
                    </li>
                    <li>
                        <span class="text-slate-500 text-sm block">Телефон</span>
                        <a href="tel:+74951234567" class="text-amber-400 font-semibold hover:underline">+7 (987) 123-45-67</a>
                    </li>
                    <li>
                        <span class="text-slate-500 text-sm block">Режим работы</span>
                        <strong class="text-white">Пн–Сб: 9:00 – 20:00</strong>
                    </li>
                    <li>
                        <span class="text-slate-500 text-sm block">Email</span>
                        <a href="mailto:info@automaster.local" class="text-amber-400 hover:underline">info@automaster.local</a>
                    </li>
                </ul>
            </div>
            <div class="rounded-2xl bg-slate-800 border border-slate-700 p-8">
                <h3 class="font-bold text-xl text-white mb-4">Почему мы</h3>
                <ul class="space-y-3 text-slate-400 text-sm">
                    <li>✓ Учёт заказов и запчастей в единой CRM</li>
                    <li>✓ Фиксация цены услуг на момент заказа</li>
                    <li>✓ Персональная скидка постоянным клиентам</li>
                    <li>✓ Уведомление о готовности в личном кабинете</li>
                </ul>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
                    <a href="<?php echo e(route('register')); ?>" class="mt-6 inline-block w-full text-center py-3 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 transition">
                        Создать аккаунт клиента
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="border-t border-slate-800 py-8 text-center text-slate-500 text-sm">
        © <?php echo e(date('Y')); ?> АвтоМастер — CRM автосервиса
    </footer>
</body>
</html>
<?php /**PATH C:\Users\1\php_project\crm\resources\views/welcome.blade.php ENDPATH**/ ?>