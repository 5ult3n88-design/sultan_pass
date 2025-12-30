# Running DeepSeek Locally (Offline) - FREE!

This guide shows you how to download and run DeepSeek AI models on your own computer - **completely FREE and offline**!

## Why Run Locally?

- ✅ **100% FREE** - No API costs ever
- ✅ **Privacy** - Your data never leaves your computer
- ✅ **Offline** - Works without internet
- ✅ **No limits** - Unlimited usage

## Option 1: Ollama (Easiest - Recommended!)

### Step 1: Install Ollama

1. Visit: **https://ollama.com**
2. Click **"Download for Mac"**
3. Open the downloaded file and drag Ollama to Applications
4. Open Ollama from Applications

### Step 2: Download DeepSeek Model

Open Terminal and run:

```bash
ollama pull deepseek-r1:7b
```

This downloads the DeepSeek model (about 4GB). It may take 5-10 minutes depending on your internet speed.

**Available Models:**
- `deepseek-r1:1.5b` - Smallest, fastest (1GB)
- `deepseek-r1:7b` - **Recommended** - Good balance (4GB)
- `deepseek-r1:14b` - Better quality (8GB)
- `deepseek-r1:32b` - Best quality, slower (19GB)

### Step 3: Test It

```bash
ollama run deepseek-r1:7b
```

You should see a chat interface. Type something like "Hello" and press Enter. If you get a response, it's working!

Type `/bye` to exit.

### Step 4: Configure Your Laravel App

Add to your `.env` file:

```env
LOCAL_AI_ENABLED=true
LOCAL_AI_BASE_URL=http://localhost:11434
LOCAL_AI_MODEL=deepseek-r1:7b
LOCAL_AI_TIMEOUT=120
```

### Step 5: Test with Your App

Run this command:

```bash
php test-local-ai.php
```

That's it! Your app now uses local DeepSeek AI!

## Option 2: LM Studio (GUI Interface)

If you prefer a graphical interface:

### Step 1: Download LM Studio

1. Visit: **https://lmstudio.ai**
2. Download for macOS
3. Install and open LM Studio

### Step 2: Download Model

1. Click **"Search"** tab
2. Search for **"deepseek"**
3. Download **DeepSeek-R1-Distill-Qwen-7B** or similar
4. Wait for download to complete

### Step 3: Start Local Server

1. Click **"Local Server"** tab
2. Select the downloaded DeepSeek model
3. Click **"Start Server"**
4. Note the URL (usually `http://localhost:1234`)

### Step 4: Configure Laravel

Update `.env`:

```env
LOCAL_AI_ENABLED=true
LOCAL_AI_BASE_URL=http://localhost:1234/v1
LOCAL_AI_MODEL=deepseek-r1-7b
LOCAL_AI_TIMEOUT=120
```

## Switching Between Local and Cloud AI

Your app can use BOTH local and cloud AI. Just change this in `.env`:

```env
# Use local AI (FREE, offline)
AI_MODE=local

# Or use cloud API (costs money, needs internet)
AI_MODE=cloud
```

## System Requirements

### Minimum (for 7B model):
- **RAM**: 8GB
- **Storage**: 10GB free
- **CPU**: Any modern processor
- **GPU**: Not required (but helps with speed)

### Recommended (for best performance):
- **RAM**: 16GB+
- **Storage**: 20GB+ free
- **CPU**: Multi-core processor
- **GPU**: NVIDIA GPU with 8GB+ VRAM (optional)

## Performance Tips

### 1. Use Smaller Model
If it's too slow, use a smaller model:
```bash
ollama pull deepseek-r1:1.5b
```

### 2. Close Other Apps
Close browser tabs and other apps to free up RAM.

### 3. Use GPU Acceleration
If you have an NVIDIA GPU, Ollama automatically uses it.

### 4. Adjust Context Size
In `.env`:
```env
LOCAL_AI_CONTEXT_SIZE=2048  # Lower = faster, less memory
```

## Troubleshooting

### "Ollama is not running"

**Solution:**
1. Open Ollama from Applications
2. Check menu bar for Ollama icon
3. Make sure it says "Ollama is running"

### "Connection refused"

**Solution:**
1. Make sure Ollama is running
2. Check the base URL in `.env` is correct: `http://localhost:11434`
3. Try: `curl http://localhost:11434` - should not give error

### Model is very slow

**Solutions:**
- Use smaller model: `ollama pull deepseek-r1:1.5b`
- Close other applications
- Check you have enough free RAM
- Consider cloud API for faster responses

### "Model not found"

**Solution:**
```bash
# List installed models
ollama list

# Pull the model again
ollama pull deepseek-r1:7b
```

## Comparing Options

| Feature | Ollama | LM Studio | Cloud API |
|---------|--------|-----------|-----------|
| **Cost** | FREE | FREE | Costs money |
| **Speed** | Fast | Medium | Very fast |
| **Internet** | Not needed | Not needed | Required |
| **Privacy** | Complete | Complete | Data sent to cloud |
| **Setup** | Easy | Very easy | Easiest |
| **Quality** | Good | Good | Excellent |

## Next Steps

1. ✅ Install Ollama
2. ✅ Download DeepSeek model
3. ✅ Update your `.env` file
4. ✅ Run `php test-local-ai.php`
5. 🎯 Start using local AI in your app!

## Updating Your Controllers

Your existing code works with BOTH local and cloud AI! Just change the service in the controller:

```php
// Use local AI
use App\Services\LocalAIService;

class AssessmentScoringController extends Controller
{
    protected LocalAIService $aiService;

    public function __construct(LocalAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    // Rest of your code stays the same!
}
```

## Support

- **Ollama Docs**: https://github.com/ollama/ollama
- **LM Studio**: https://lmstudio.ai/docs
- **DeepSeek Models**: https://huggingface.co/deepseek-ai

## Summary

**You CAN download and run DeepSeek for FREE on your computer!**

The easiest way:
1. Install Ollama
2. Run: `ollama pull deepseek-r1:7b`
3. Update `.env`: `LOCAL_AI_ENABLED=true`
4. Done! 🎉

No API keys, no costs, completely offline!
