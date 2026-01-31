</main>
<footer class="relative bg-[#0f172a] text-slate-400 pt-16 pb-8 border-t border-slate-800 overflow-hidden mt-auto">
    
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl -translate-y-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl translate-y-1/2 pointer-events-none"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="space-y-6">
                <div class="flex items-center gap-3">
                    <?php if(!empty($web_config->site_logo)): ?>
                        <div class="p-1 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full">
                            <img src="<?php echo $web_config->site_logo; ?>" class="h-10 w-10 rounded-full bg-slate-900 border-2 border-slate-900 object-cover">
                        </div>
                    <?php endif; ?>
                    <div>
                        <h3 class="text-2xl font-bold text-white tracking-wide"><?php echo $web_config->site_name; ?></h3>
                        <span class="text-xs text-blue-400 font-medium px-2 py-0.5 bg-blue-500/10 rounded border border-blue-500/20">Official Store</span>
                    </div>
                </div>
                
                <p class="text-slate-400 leading-relaxed text-sm pr-4">
                    <?php 
                        // ถ้ามีข้อมูลใน Database ให้แสดง ถ้าไม่มีให้แสดงข้อความเดิม
                        echo !empty($web_config->site_about) 
                        ? nl2br(htmlspecialchars($web_config->site_about)) 
                        : 'ร้านค้าออนไลน์ระบบอัตโนมัติ 24 ชั่วโมง จำหน่ายไอดีเกม บัตรเติมเงิน และสินค้าดิจิตอลคุณภาพสูง ปลอดภัย 100%'; 
                    ?>
                </p>

                <div class="pt-2">
                    <h5 class="text-white text-sm font-semibold mb-3">รับชำระเงินผ่าน</h5>
                    <div class="flex gap-3">
                        <div class="bg-white p-1.5 rounded-lg shadow-lg hover:scale-105 transition duration-300">
                            <img src="https://images.seeklogo.com/logo-png/36/1/truemoney-wallet-logo-png_seeklogo-367826.png" class="h-6 w-auto">
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:pl-10">
                <h4 class="text-white font-bold text-lg mb-6 border-l-4 border-blue-500 pl-3">ช่องทางติดตาม</h4>
                <ul class="space-y-3">
                    <?php 
                    $social_links = [
                        ['url' => $web_config->facebook_url, 'icon' => 'fa-facebook', 'name' => 'Facebook Page', 'color' => 'hover:text-blue-500'],
                        ['url' => $web_config->line_url, 'icon' => 'fa-line', 'name' => 'Line Official', 'color' => 'hover:text-green-500'],
                        ['url' => $web_config->youtube_url, 'icon' => 'fa-youtube', 'name' => 'YouTube Channel', 'color' => 'hover:text-red-500'],
                        ['url' => $web_config->tiktok_url, 'icon' => 'fa-tiktok', 'name' => 'TikTok', 'color' => 'hover:text-pink-500'],
                        ['url' => $web_config->instagram_url, 'icon' => 'fa-instagram', 'name' => 'Instagram', 'color' => 'hover:text-purple-500'],
                    ];
                    
                    foreach($social_links as $link): 
                        if(!empty($link['url'])):
                    ?>
                    <li>
                        <a href="<?php echo $link['url']; ?>" target="_blank" class="group flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800/50 transition-all duration-300 <?php echo $link['color']; ?>">
                            <span class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center group-hover:bg-white/10 transition">
                                <i class="fa-brands <?php echo $link['icon']; ?>"></i>
                            </span>
                            <span class="text-sm font-medium"><?php echo $link['name']; ?></span>
                        </a>
                    </li>
                    <?php endif; endforeach; ?>
                </ul>
            </div>

            <div>
                <?php if(!empty($web_config->discord_widget_id)): ?>
                    <div class="relative group max-w-[350px] ml-auto lg:mr-0 mr-auto">
                        <div class="absolute -inset-1 bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-500"></div>
                        
                        <div class="relative bg-[#1e2124] rounded-xl overflow-hidden border border-slate-700 shadow-2xl">
                            <div class="flex items-center justify-between px-4 py-2 bg-[#282b30] border-b border-slate-700 h-12">
                                <div class="flex items-center gap-2">
                                    <i class="fa-brands fa-discord text-[#5865F2]"></i>
                                    <span class="text-white text-sm font-bold">Community Server</span>
                                </div>
                                <span class="flex h-2 w-2 relative">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                            </div>
                            <iframe 
                                src="https://discord.com/widget?id=<?php echo $web_config->discord_widget_id; ?>&theme=dark" 
                                width="100%"  
                                height="300" 
                                allowtransparency="true" 
                                frameborder="0" 
                                sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
                                class="w-full bg-[#1e2124]"
                            ></iframe>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="h-full min-h-[150px] flex items-center justify-center bg-slate-800/50 rounded-xl border border-dashed border-slate-700 max-w-[350px] ml-auto lg:mr-0 mr-auto">
                        <div class="text-center p-6">
                            <i class="fa-brands fa-discord text-4xl text-slate-600 mb-3"></i>
                            <p class="text-slate-500 text-sm">Join our Community</p>
                            <?php if(!empty($web_config->discord_url)): ?>
                                <a href="<?php echo $web_config->discord_url; ?>" class="mt-3 inline-block px-4 py-2 bg-[#5865F2] hover:bg-[#4752c4] text-white text-sm rounded transition">เข้าร่วม Discord</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="border-t border-slate-800/60 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-slate-500">
            <p>&copy; <?php echo date("Y"); ?> <span class="text-slate-300"><?php echo $web_config->site_name; ?></span>. All Rights Reserved.</p>
            <div class="flex gap-4 mt-4 md:mt-0">
                <a href="#" class="hover:text-white transition">Terms of Service</a>
                <a href="#" class="hover:text-white transition">Privacy Policy</a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>