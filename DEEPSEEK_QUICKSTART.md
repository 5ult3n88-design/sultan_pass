# DeepSeek Quick Start Guide

## What is DeepSeek?

DeepSeek is a cloud-based AI service (like ChatGPT or Claude) that provides advanced language understanding and generation capabilities. You **don't download it** - instead, you:
1. Sign up for an account
2. Get an API key
3. Make API calls from your application

Think of it like using Google Maps API - you don't download Google Maps, you just call their service with your API key.

## Getting Started (5 Minutes)

### Step 1: Create DeepSeek Account

1. Visit: **https://platform.deepseek.com**
2. Click **"Sign Up"** or **"Register"**
3. Fill in your details (email, password, etc.)
4. Verify your email address
5. Log in to your account

### Step 2: Get Your API Key

1. Once logged in, go to your **Dashboard**
2. Look for **"API Keys"**, **"Keys"**, or **"Credentials"** menu
3. Click **"Create API Key"** or **"New API Key"**
4. Copy the key (it looks like: `sk-1234567890abcdef...`)
5. ⚠️ **Save it immediately** - you may not see it again!

### Step 3: Add API Key to Your Project

Open your `.env` file and replace this line:

```env
DEEPSEEK_API_KEY=your_deepseek_api_key_here
```

With your actual API key:

```env
DEEPSEEK_API_KEY=sk-1234567890abcdef1234567890abcdef
```

Save the file.

### Step 4: Clear Laravel Cache

Run this command in your terminal:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/pass-main
php artisan config:clear
```

### Step 5: Test the Integration

Run the test script:

```bash
php test-deepseek.php
```

If everything is working, you'll see:
```
✓ All tests passed! DeepSeek is working correctly.
```

## Common Issues & Solutions

### Issue 1: "API key is not configured"

**Solution:**
1. Make sure you updated `.env` file with your real API key
2. Run: `php artisan config:clear`
3. Try again

### Issue 2: "DeepSeek API request failed: 401 Unauthorized"

**Solution:**
- Your API key is invalid or expired
- Double-check you copied the entire key correctly
- Generate a new API key from DeepSeek dashboard

### Issue 3: "DeepSeek API request failed: 429 Too Many Requests"

**Solution:**
- You've exceeded your rate limit
- Wait a few minutes and try again
- Check your DeepSeek account quotas/limits
- Consider upgrading your plan if needed

### Issue 4: Timeout errors

**Solution:**
- Increase timeout in `.env`: `DEEPSEEK_TIMEOUT=60`
- Check your internet connection
- Check DeepSeek service status

## Pricing Information

DeepSeek typically offers:
- **Free Tier**: Limited API calls per month (great for testing)
- **Paid Tiers**: Pay-as-you-go or subscription plans

Check their pricing page: **https://platform.deepseek.com/pricing**

## Alternative: If DeepSeek is Not Available

If you can't access DeepSeek or prefer a different AI service, the code can be adapted for:

### Option 1: OpenAI (ChatGPT)
- Sign up: https://platform.openai.com
- Get API key from: https://platform.openai.com/api-keys
- Widely available, well-documented
- Update the service to use OpenAI endpoints

### Option 2: Anthropic (Claude)
- Sign up: https://console.anthropic.com
- Get API key from console
- Strong reasoning capabilities
- Update the service to use Anthropic endpoints

### Option 3: Google Gemini
- Sign up: https://makersuite.google.com
- Get API key
- Free tier available
- Update the service to use Google AI endpoints

**Note:** The code structure I created can easily be adapted to any AI provider - you'd just need to modify the API endpoints and request formats in `DeepSeekService.php`.

## What's Included in Your Integration

Your eAssess platform now has:

1. ✅ **Automatic Assessment Scoring** - AI scores candidate responses
2. ✅ **Qualitative Analysis** - AI analyzes open-ended text
3. ✅ **Report Generation** - AI creates professional narratives
4. ✅ **Recommendations** - AI suggests development plans
5. ✅ **Strengths/Weaknesses** - AI identifies key areas

## Using the Features

### Quick Example: Score an Assessment

```php
use App\Services\DeepSeekService;

$deepSeek = new DeepSeekService();

$result = $deepSeek->scoreAssessment([
    'title' => 'Leadership Assessment',
    'type' => 'psychometric',
    'competencies' => [
        ['name' => 'Leadership', 'weight' => 0.3, 'description' => 'Team leadership']
    ],
    'responses' => [
        [
            'question' => 'Describe your leadership style.',
            'answer' => 'I believe in servant leadership...'
        ]
    ]
]);

echo $result['overall_score']; // e.g., 85
```

### API Endpoints

All ready to use at:
- `POST /api/ai/assessments/score` - Score assessments
- `POST /api/ai/responses/analyze` - Analyze text
- `POST /api/ai/assessments/strengths-weaknesses` - Find strengths
- `POST /api/ai/reports/generate-narrative` - Generate reports
- `POST /api/ai/recommendations/generate` - Get recommendations

See `DEEPSEEK_USAGE.md` for detailed examples.

## Next Steps

1. ✅ Get your DeepSeek API key (follow steps above)
2. ✅ Update `.env` file
3. ✅ Run test script: `php test-deepseek.php`
4. 📖 Read `DEEPSEEK_USAGE.md` for integration examples
5. 🎯 Start using AI in your assessment workflows!

## Support

- **DeepSeek Documentation**: https://platform.deepseek.com/docs
- **DeepSeek Support**: Check their platform for support options
- **Integration Issues**: Review the detailed `DEEPSEEK_USAGE.md` file

## Summary

**You don't download DeepSeek** - it's a cloud service like Gmail or Google Drive. You just:
1. Create an account at platform.deepseek.com
2. Get an API key
3. Add the key to your `.env` file
4. Your Laravel app calls their API over the internet

That's it! The integration is already complete in your code. You just need the API key to activate it.
